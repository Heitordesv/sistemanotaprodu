<?php

namespace App\Services;

use App\Models\Usuario;
use Carbon\CarbonImmutable;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Str;
use JsonException;

class PdvTokenService
{
    public const VERSION = 1;

    public function issue(Usuario $usuario): array
    {
        $now = CarbonImmutable::now();
        $ttlMinutes = max(5, (int) config('pdv.token_ttl_minutes', 720));
        $expiresAt = $now->addMinutes($ttlMinutes);

        $payload = [
            'v' => self::VERSION,
            'uid' => (int) $usuario->id,
            'eid' => (int) $usuario->empresa_id,
            'iat' => $now->timestamp,
            'exp' => $expiresAt->timestamp,
            'nonce' => Str::random(32),
        ];

        return [
            'token' => Crypt::encryptString(json_encode($payload, JSON_THROW_ON_ERROR)),
            'expires_at' => $expiresAt->toIso8601String(),
            'expires_in' => $expiresAt->timestamp - $now->timestamp,
        ];
    }

    /**
     * @throws AuthenticationException
     */
    public function authenticate(?string $token): Usuario
    {
        $token = trim((string) $token);

        if ($token === '' || strlen($token) > 8192) {
            $this->fail();
        }

        try {
            $payload = json_decode(
                Crypt::decryptString($token),
                true,
                512,
                JSON_THROW_ON_ERROR
            );
        } catch (DecryptException|JsonException $e) {
            $this->fail();
        }

        if (!$this->validPayload($payload)) {
            $this->fail();
        }

        $now = CarbonImmutable::now()->timestamp;
        $clockSkew = max(0, (int) config('pdv.clock_skew_seconds', 300));

        if ((int) $payload['exp'] <= $now || (int) $payload['iat'] > ($now + $clockSkew)) {
            $this->fail();
        }

        $usuario = Usuario::query()
            ->where('id', (int) $payload['uid'])
            ->where('empresa_id', (int) $payload['eid'])
            ->first();

        if (!$usuario) {
            $this->fail();
        }

        // Preserve legacy rows where ativo may still be NULL, but reject users
        // explicitly disabled after the token was issued.
        if ($usuario->ativo !== null && (int) $usuario->ativo === 0) {
            $this->fail();
        }

        return $usuario;
    }

    private function validPayload($payload): bool
    {
        if (!is_array($payload)) {
            return false;
        }

        foreach (['v', 'uid', 'eid', 'iat', 'exp', 'nonce'] as $key) {
            if (!array_key_exists($key, $payload)) {
                return false;
            }
        }

        return
            (int) $payload['v'] === self::VERSION &&
            (int) $payload['uid'] > 0 &&
            (int) $payload['eid'] > 0 &&
            (int) $payload['iat'] > 0 &&
            (int) $payload['exp'] > (int) $payload['iat'] &&
            is_string($payload['nonce']) &&
            strlen($payload['nonce']) >= 16;
    }

    /**
     * @return never
     * @throws AuthenticationException
     */
    private function fail(): void
    {
        throw new AuthenticationException('Credencial PDV inválida ou expirada.');
    }
}
