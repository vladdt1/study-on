<?php
namespace App\Service;

class JwtDecoder
{
    public function decode(string $jwt): array
    {
        $payload = json_decode(base64_decode(str_replace('_', '/', str_replace('-', '+', explode('.', $jwt)[1]))), true);
        return $payload;
    }
}
