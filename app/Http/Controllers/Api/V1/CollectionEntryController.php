<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Enums\PaymentMethod;
use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\StoreCollectionEntryRequest;
use App\Http\Resources\CollectionEntryResource;
use App\Models\CollectionEntry;
use App\Services\CollectionEntryService;
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
    public function __construct(private readonly CollectionEntryService $collectionEntries) {}

    #[OA\Post(
        path: '/collection-entries',
        tags: ['Collection Entries'],
        summary: 'Record a payment collected from a dealer',
        security: [['sanctum' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['dealer_id', 'amount', 'payment_method'],
                properties: [
                    new OA\Property(property: 'dealer_id', type: 'integer'),
                    new OA\Property(property: 'collection_date', type: 'string', format: 'date', nullable: true),
                    new OA\Property(property: 'amount', type: 'number', format: 'float'),
                    new OA\Property(property: 'payment_method', type: 'string', enum: ['cash', 'cheque', 'bank_transfer', 'mobile_banking']),
                    new OA\Property(property: 'reference_no', type: 'string', nullable: true),
                    new OA\Property(property: 'remarks', type: 'string', nullable: true),
                ],
            ),
        ),
        responses: [
            new OA\Response(response: 201, description: 'Collection recorded'),
            new OA\Response(response: 422, description: 'Validation error, or the amount exceeds the outstanding balance tolerance'),
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
