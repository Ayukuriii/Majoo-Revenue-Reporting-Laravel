<?php

namespace App\Services;

use App\Exceptions\AccountHasNoMerchantException;
use App\Models\User;
use App\Support\Tenant;
use PHPOpenSourceSaver\JWTAuth\JWTGuard;
use Symfony\Component\HttpFoundation\Response;

class AuthService
{
    /**
     * Attempt credentials and issue a JWT.
     *
     * @param  array{email: string, password: string}  $data
     * @return array{token: string, token_type: string, expires_in: int}
     *
     * @throws \Exception
     * @throws AccountHasNoMerchantException
     */
    public function login(array $data): array
    {
        $token = $this->guard()->attempt([
            'email' => $data['email'],
            'password' => $data['password'],
        ]);

        if (! is_string($token) || $token === '') {
            throw new \Exception('Invalid credentials', Response::HTTP_UNAUTHORIZED);
        }

        $user = $this->guard()->user();

        if (! $user instanceof User || $user->merchant === null) {
            $this->guard()->logout();

            throw new AccountHasNoMerchantException;
        }

        return $this->tokenPayload($token);
    }

    /**
     * Invalidate the current JWT (blacklist).
     */
    public function logout(): void
    {
        $this->guard()->logout();
    }

    /**
     * Issue a new JWT from the current token.
     *
     * @return array{token: string, token_type: string, expires_in: int}
     */
    public function refresh(): array
    {
        return $this->tokenPayload($this->guard()->refresh());
    }

    /**
     * Authenticated user for the current token.
     *
     * @throws \Exception
     */
    public function currentUser(): User
    {
        $user = $this->guard()->user();

        if (! $user instanceof User) {
            throw new \Exception('User not found', Response::HTTP_NOT_FOUND);
        }

        $user->loadMissing('merchant');

        return $user;
    }

    /**
     * Merchant id for the authenticated user (never from the request).
     *
     * @throws AccountHasNoMerchantException
     */
    public function currentMerchantId(): int
    {
        return Tenant::currentMerchantId();
    }

    /**
     * @return array{token: string, token_type: string, expires_in: int}
     */
    private function tokenPayload(string $token): array
    {
        $ttlMinutes = $this->guard()->getTTL() ?? (int) config('jwt.ttl');

        return [
            'token' => $token,
            'token_type' => 'bearer',
            'expires_in' => $ttlMinutes * 60,
        ];
    }

    private function guard(): JWTGuard
    {
        $guard = auth('api');

        if (! $guard instanceof JWTGuard) {
            throw new \RuntimeException('API auth guard is not a JWT guard');
        }

        return $guard;
    }
}
