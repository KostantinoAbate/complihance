<?php

namespace KostantinoAbate\Complihance\Services\Cookies\Scanner;

class SetCookieHeaderParser
{
    public function parse(string $header, string $url): ?array
    {
        $parts = explode(';', $header);

        $nameValue = trim(array_shift($parts));

        if (! str_contains($nameValue, '=')) {
            return null;
        }

        [$name] = explode('=', $nameValue, 2);

        $attributes = collect($parts)
            ->mapWithKeys(function (string $part) {
                $part = trim($part);

                if (! str_contains($part, '=')) {
                    return [strtolower($part) => true];
                }

                [$key, $value] = explode('=', $part, 2);

                return [strtolower($key) => trim($value)];
            });

        return [
            'name' => trim($name),
            'domain' => $attributes->get('domain'),
            'path' => $attributes->get('path', '/'),
            'url' => $url,
            'secure' => $attributes->has('secure'),
            'http_only' => $attributes->has('httponly'),
            'same_site' => $attributes->get('samesite'),
            'expires_at' => $attributes->has('expires')
                ? now()->parse($attributes->get('expires'))
                : null,
        ];
    }
}
