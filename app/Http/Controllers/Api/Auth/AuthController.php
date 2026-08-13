<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Auth\LoginRequest;
use App\Http\Resources\Api\User\UserResource;
use App\Services\AuthService;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class AuthController extends Controller
{
    public function __construct(private readonly AuthService $authService) {}

    /**
     * Exchange email and password for a JWT.
     */
    public function login(LoginRequest $request): JsonResponse
    {
        try {
            $token = $this->authService->login($request->validated());

            return respondWithData(
                data: $token,
                message: 'Login successful',
            );
        } catch (\Exception $e) {
            return respondError(
                error: $e->getMessage(),
                statusCode: $this->statusCodeFromException($e),
            );
        }
    }

    /**
     * Invalidate the current JWT.
     */
    public function logout(): JsonResponse
    {
        try {
            $this->authService->logout();

            return respondWithMessage(message: 'Logged out successfully');
        } catch (\Exception $e) {
            return respondError(
                error: $e->getMessage(),
                statusCode: $this->statusCodeFromException($e),
            );
        }
    }

    /**
     * Issue a new JWT from the current token.
     */
    public function refresh(): JsonResponse
    {
        try {
            $token = $this->authService->refresh();

            return respondWithData(
                data: $token,
                message: 'Token refreshed successfully',
            );
        } catch (\Exception $e) {
            return respondError(
                error: $e->getMessage(),
                statusCode: $this->statusCodeFromException($e),
            );
        }
    }

    /**
     * Current authenticated user.
     */
    public function me(): JsonResponse
    {
        try {
            $user = $this->authService->currentUser();

            return respondWithData(
                data: new UserResource($user),
                message: 'User data retrieved successfully',
            );
        } catch (\Exception $e) {
            return respondError(
                error: $e->getMessage(),
                statusCode: $this->statusCodeFromException($e),
            );
        }
    }

    private function statusCodeFromException(\Exception $e): int
    {
        $code = $e->getCode();

        if (is_int($code) && $code >= 400 && $code < 600) {
            return $code;
        }

        return Response::HTTP_INTERNAL_SERVER_ERROR;
    }
}
