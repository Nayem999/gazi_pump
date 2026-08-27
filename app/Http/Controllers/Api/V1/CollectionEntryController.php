<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Enums\PaymentMethod;
use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\SendCollectionOtpRequest;
use App\Http\Requests\Api\V1\StoreCollectionEntryRequest;
use App\Http\Resources\CollectionEntryResource;
use App\Models\CollectionEntry;
use App\Services\CollectionEntryService;
use App\Services\CollectionOtpService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

/**
 * Self-service field collection entry for the mobile app: a Sales Executive
 * records a payment received from a dealer and pulls back their own
 * collection history. Cross-rep collection reporting is an Admin Dashboard
 * concern (Module 9 web CRUD).
 */
class CollectionEntryController extends Controller
{
    public function __construct(
        private readonly CollectionEntryService $collectionEntries,
        private readonly CollectionOtpService $otps,
    ) {}

    #[OA\Post(
        path: '/collection-entries/send-otp',
        tags: ['Collection Entries'],
        summary: 'Send (or, in demo mode, generate) an OTP to confirm a collection with the dealer',
        security: [['sanctum' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['dealer_id', 'amount', 'payment_method'],
                properties: [
                    new OA\Property(property: 'dealer_id', type: 'integer'),
                    new OA\Property(property: 'amount', type: 'number', format: 'float'),
                    new OA\Property(property: 'payment_method', type: 'string', enum: ['cash', 'cheque', 'bank_transfer', 'mobile_banking']),
                ],
            ),
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'OTP generated — sent by SMS, or (when no gateway is configured) returned directly as demo_code',
            ),
        ],
    )]
    public function sendOtp(SendCollectionOtpRequest $request): JsonResponse
    {
        $result = $this->otps->send(
            $request->user(),
            (int) $request->input('dealer_id'),
            (float) $request->input('amount'),
            PaymentMethod::from($request->string('payment_method')->toString()),
        );

        return ApiResponse::success($result, $result['sent'] ? 'OTP sent to the dealer.' : 'OTP generated (demo mode — SMS gateway not configured).');
    }

    #[OA\Post(
        path: '/collection-entries',
        tags: ['Collection Entries'],
        summary: 'Record a payment collected from a dealer',
        security: [['sanctum' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\MediaType(mediaType: 'multipart/form-data', schema: new OA\Schema(
                required: ['dealer_id', 'amount', 'payment_method', 'otp_id', 'otp_code'],
                properties: [
                    new OA\Property(property: 'dealer_id', type: 'integer'),
                    new OA\Property(property: 'collection_date', type: 'string', format: 'date', nullable: true),
                    new OA\Property(property: 'amount', type: 'number', format: 'float'),
                    new OA\Property(property: 'payment_method', type: 'string', enum: ['cash', 'cheque', 'bank_transfer', 'mobile_banking']),
                    new OA\Property(property: 'reference_no', type: 'string', nullable: true),
                    new OA\Property(property: 'cheque_image', type: 'string', format: 'binary', nullable: true, description: 'Required when payment_method is cheque'),
                    new OA\Property(property: 'otp_id', type: 'integer', description: 'From /collection-entries/send-otp — a collection cannot be recorded without a verified OTP'),
                    new OA\Property(property: 'otp_code', type: 'string', description: 'The 6-digit code the dealer read back to the executive'),
                    new OA\Property(property: 'remarks', type: 'string', nullable: true),
                ],
            )),
        ),
        responses: [
            new OA\Response(response: 201, description: 'Collection recorded'),
            new OA\Response(response: 422, description: 'Validation error, a missing/invalid/expired OTP, or the amount exceeds the outstanding balance tolerance'),
        ],
    )]
    public function store(StoreCollectionEntryRequest $request): JsonResponse
    {
        $entry = $this->collectionEntries->recordCollection(
            $request->user(),
            (int) $request->input('dealer_id'),
            (float) $request->input('amount'),
            PaymentMethod::from($request->string('payment_method')->toString()),
            $request->input('reference_no'),
            $request->input('remarks'),
            $request->input('collection_date'),
            $request->file('cheque_image'),
            $request->integer('otp_id') ?: null,
            $request->input('otp_code'),
        );

        return ApiResponse::success(new CollectionEntryResource($entry), 'Collection recorded.', 201);
    }

    #[OA\Get(
        path: '/collection-entries',
        tags: ['Collection Entries'],
        summary: "List the authenticated user's own collection history",
        security: [['sanctum' => []]],
        parameters: [
            new OA\Parameter(name: 'date_from', in: 'query', required: false, schema: new OA\Schema(type: 'string', format: 'date')),
            new OA\Parameter(name: 'date_to', in: 'query', required: false, schema: new OA\Schema(type: 'string', format: 'date')),
            new OA\Parameter(name: 'status', in: 'query', required: false, description: 'Filter by approval status', schema: new OA\Schema(type: 'string', enum: ['pending', 'approved', 'rejected'])),
            new OA\Parameter(name: 'per_page', in: 'query', required: false, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [new OA\Response(response: 200, description: 'Paginated collection history')],
    )]
    public function index(Request $request): JsonResponse
    {
        $query = CollectionEntry::where('user_id', $request->user()->id)->with(['dealer']);

        if ($request->filled('date_from')) {
            $query->whereDate('collection_date', '>=', $request->string('date_from'));
        }

        if ($request->filled('date_to')) {
            $query->whereDate('collection_date', '<=', $request->string('date_to'));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->string('status'));
        }

        $collectionEntries = $query->latest('collection_date')->paginate((int) $request->integer('per_page', 20));

        return ApiResponse::success(
            CollectionEntryResource::collection($collectionEntries->items()),
            'Collection history retrieved.',
            200,
            [
                'current_page' => $collectionEntries->currentPage(),
                'per_page' => $collectionEntries->perPage(),
                'total' => $collectionEntries->total(),
                'last_page' => $collectionEntries->lastPage(),
            ],
        );
    }
}
