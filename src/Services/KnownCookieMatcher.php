<?php

namespace KostantinoAbate\Complihance\Services;

use Illuminate\Support\Arr;

class KnownCookieMatcher
{
    public function match(string $name): ?array
    {
        if ($name === config('session.cookie')) {
            return [
                'category' => 'necessary',
                'vendor' => 'Laravel',
                'duration' => 'Session',
                'pattern' => '^'.preg_quote($name, '/').'$',
                'translations' => [
                    'en' => [
                        'name' => $name,
                        'description' => 'Maintains the user session.',
                    ],
                    'it' => [
                        'name' => $name,
                        'description' => 'Mantiene la sessione dell’utente.',
                    ],
                ],
            ];
        }

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
