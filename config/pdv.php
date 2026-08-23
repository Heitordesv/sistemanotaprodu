<?php

return [
    /*
    |--------------------------------------------------------------------------
    | PDV authentication token lifetime
    |--------------------------------------------------------------------------
    |
    | Tokens are intentionally short lived compared with the old permanent
    | Base64 credential. Twelve hours covers a normal work shift and can be
    | adjusted per environment without changing the token contract.
    |
    */
    'token_ttl_minutes' => (int) env('PDV_TOKEN_TTL_MINUTES', 720),
    'clock_skew_seconds' => (int) env('PDV_TOKEN_CLOCK_SKEW_SECONDS', 300),
];
