<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\LoginRequest;
use App\Http\Resources\UserResource;
use App\Services\AuthService;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use OpenApi\Attributes as OA;

#[OA\Info(
    title: 'Gazi Pump SFA API',
    version: '1.0.0',
    description: 'Mobile REST API for the Gazi Pump Sales Force Automation system.'
)]
#[OA\Server(url: '/api/v1', description: 'API v1')]
#[OA\SecurityScheme(
    securityScheme: 'sanctum',
    type: 'http',
    scheme: 'bearer',
    bearerFormat: 'Sanctum token'
)]
class AuthController extends Controller
{
    public function __construct(private readonly AuthService $auth) {}

    #[OA\Post(
        path: '/auth/login',
        tags: ['Authentication'],
        summary: 'Authenticate and receive a Sanctum API token',
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['email', 'password', 'device_name'],
                properties: [
                    new OA\Property(property: 'email', type: 'string', format: 'email', example: 'admin@gazipump.com'),
                    new OA\Property(property: 'password', type: 'string', format: 'password', example: 'password'),
                    new OA\Property(property: 'device_name', type: 'string', example: 'iPhone 15'),
                ],
            ),
        ),
        responses: [
            new OA\Response(response: 200, description: 'Login successful'),
            new OA\Response(response: 422, description: 'Validation error / invalid credentials'),
        ],
    )]
    public function login(LoginRequest $request): JsonResponse
    {
        $throttleKey = Str::transliterate(Str::lower($request->string('email')).'|'.$request->ip());

        $result = $this->auth->login(
            $request->string('email')->toString(),
            $request->string('password')->toString(),
            $request->string('device_name')->toString(),
            $throttleKey,
        );

        return ApiResponse::success([
            'user' => new UserResource($result['user']),
            'token' => $result['token']->plainTextToken,
        ], 'Login successful.');
    }

    #[OA\Get(
        path: '/auth/me',
        tags: ['Authentication'],
        summary: "Get the authenticated user's profile",
        security: [['sanctum' => []]],
        responses: [new OA\Response(response: 200, description: 'Authenticated user')],
    )]
    public function me(Request $request): JsonResponse
    {
        return ApiResponse::success(new UserResource($request->user()));
    }

    #[OA\Post(
        path: '/auth/logout',
        tags: ['Authentication'],
        summary: 'Revoke the current API token',
        security: [['sanctum' => []]],
        responses: [new OA\Response(response: 200, description: 'Logged out')],
    )]
    public function logout(Request $request): JsonResponse
    {
        /** @var Authenticatable $user */
        $user = $request->user();

        $this->auth->logout($user);

        return ApiResponse::success(null, 'Logged out successfully.');
    }
}
