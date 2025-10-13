<?php

namespace App\Core;

class Csrf
{
    private const TOKEN_KEY = '_csrf_token';

    public function __construct(private Session $session, private string $secret)
    {
    }

    public function token(): string
    {
        $token = $this->session->get(self::TOKEN_KEY);
        if (!$token) {
            $token = hash_hmac('sha256', bin2hex(random_bytes(16)), $this->secret);
            $this->session->put(self::TOKEN_KEY, $token);
        }
        return $token;
    }

    public function validate(?string $token): bool
    {
        $sessionToken = $this->session->get(self::TOKEN_KEY);
        return $sessionToken !== null && hash_equals($sessionToken, (string) $token);
    }
}
