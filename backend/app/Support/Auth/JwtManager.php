<?php

namespace App\Support\Auth;

use App\Models\RefreshToken;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Support\Str;

class JwtManager
{
    public function issueAccessToken(User $user): string
    {
        $now = CarbonImmutable::now();
        $ttl = (int) config('erp.jwt.ttl', 60);

        return $this->encode([
            'iss' => config('erp.jwt.issuer'),
            'sub' => $user->getAuthIdentifier(),
            'school_id' => $user->school_id,
            'email' => $user->email,
            'iat' => $now->timestamp,
            'exp' => $now->addMinutes($ttl)->timestamp,
            'jti' => (string) Str::uuid(),
        ]);
    }

    public function issueRefreshToken(User $user): string
    {
        $plainToken = Str::random(64);
        $tokenId = (string) Str::uuid();

        RefreshToken::create([
            'school_id' => $user->school_id,
            'user_id' => $user->id,
            'token_id' => $tokenId,
            'token_hash' => hash('sha256', $plainToken),
            'expires_at' => now()->addMinutes((int) config('erp.jwt.refresh_ttl', 10080)),
        ]);

        return $tokenId.'.'.$plainToken;
    }

    public function decode(string $token): array
    {
        $segments = explode('.', $token);
        abort_if(count($segments) !== 3, 401, 'Malformed token.');

        [$encodedHeader, $encodedPayload, $signature] = $segments;

        $expectedSignature = $this->sign($encodedHeader.'.'.$encodedPayload);

        if (! hash_equals($expectedSignature, $signature)) {
            abort(401, 'Invalid token signature.');
        }

        $payload = json_decode(base64_decode(strtr($encodedPayload, '-_', '+/')), true, 512, JSON_THROW_ON_ERROR);

        if (($payload['exp'] ?? 0) < now()->timestamp) {
            abort(401, 'Token has expired.');
        }

        return $payload;
    }

    public function refresh(string $refreshToken): array
    {
        [$tokenId, $plainToken] = explode('.', $refreshToken, 2);

        /** @var RefreshToken|null $record */
        $record = RefreshToken::query()
            ->withoutGlobalScopes()
            ->where('token_id', $tokenId)
            ->first();

        if (! $record || $record->revoked_at || $record->expires_at->isPast()) {
            abort(401, 'Refresh token is invalid.');
        }

        if (! hash_equals($record->token_hash, hash('sha256', $plainToken))) {
            abort(401, 'Refresh token is invalid.');
        }

        $record->update([
            'last_used_at' => now(),
            'revoked_at' => now(),
        ]);

        $user = $record->user()->firstOrFail();

        return [
            'access_token' => $this->issueAccessToken($user),
            'refresh_token' => $this->issueRefreshToken($user),
            'user' => $user,
        ];
    }

    public function revokeAllForUser(User $user): void
    {
        $user->refreshTokens()->update(['revoked_at' => now()]);
    }

    protected function encode(array $payload): string
    {
        $header = ['alg' => 'HS256', 'typ' => 'JWT'];

        $encodedHeader = $this->base64UrlEncode(json_encode($header, JSON_THROW_ON_ERROR));
        $encodedPayload = $this->base64UrlEncode(json_encode($payload, JSON_THROW_ON_ERROR));
        $signature = $this->sign($encodedHeader.'.'.$encodedPayload);

        return $encodedHeader.'.'.$encodedPayload.'.'.$signature;
    }

    protected function sign(string $payload): string
    {
        return $this->base64UrlEncode(hash_hmac('sha256', $payload, (string) config('erp.jwt.secret'), true));
    }

    protected function base64UrlEncode(string $value): string
    {
        return rtrim(strtr(base64_encode($value), '+/', '-_'), '=');
    }
}
