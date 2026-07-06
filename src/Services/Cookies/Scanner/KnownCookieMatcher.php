<?php

namespace KostantinoAbate\Complihance\Services\Cookies\Scanner;

use Illuminate\Support\Arr;

class KnownCookieMatcher
{
    public function match(string $name): ?array
    {
        foreach ($this->patterns() as $cookie) {
            if (preg_match($cookie['pattern'], $name) === 1) {
                return [
                    ...Arr::except($cookie, ['pattern']),
                    'pattern' => trim($cookie['pattern'], '/'),
                ];
            }
        }

        return null;
    }

    protected function patterns(): array
    {
        return require __DIR__.'/../Support/known-cookie-patterns.php';
    }
}
