<?php

namespace KostantinoAbate\Complihance\Services\Cookies\Scanner;

use Illuminate\Support\Carbon;
use Throwable;

class SetCookieHeaderParser
{
    /**
     * Parse a Set-Cookie header into a normalized cookie payload.
     *
     * @return array{
     *     name: string,
     *     domain: string|null,
     *     path: string,
     *     url: string,
     *     secure: bool,
     *     http_only: bool,
     *     same_site: string|null,
     *     expires_at: Carbon|null
     * }|null
     */
    public function parse(string $header, string $url): ?array
    {
        $parts = explode(';', $header);
        $nameValue = trim((string) array_shift($parts));

        if (! str_contains($nameValue, '=')) {
            return null;
        }

        [$name] = explode('=', $nameValue, 2);

        $attributes = collect($parts)
            ->mapWithKeys(function (string $part): array {
                $part = trim($part);

                if (! str_contains($part, '=')) {
                    return [strtolower($part) => true];
                }

                [$key, $value] = explode('=', $part, 2);

                return [strtolower(trim($key)) => trim($value)];
            });

        return [
            'name' => trim($name),
            'domain' => $attributes->get('domain'),
            'path' => $attributes->get('path', '/'),
            'url' => $url,
            'secure' => $attributes->has('secure'),
            'http_only' => $attributes->has('httponly'),
            'same_site' => $attributes->get('samesite'),
            'expires_at' => $this->parseExpiresAt($attributes->get('expires')),
        ];
    }

    /**
     * Parse the cookie expiration date when available.
     */
    protected function parseExpiresAt(mixed $value): ?Carbon
    {
        if (! is_string($value) || trim($value) === '') {
            return null;
        }

        try {
            return Carbon::parse($value);
        } catch (Throwable) {
            return null;
        }
    }
}
