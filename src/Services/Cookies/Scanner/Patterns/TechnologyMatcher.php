<?php

namespace KostantinoAbate\Complihance\Services\Cookies\Scanner\Patterns;

use Illuminate\Support\Arr;

class TechnologyMatcher
{
    public function match(string $type, string $value): ?array
    {
        foreach ($this->patterns() as $technology) {
            if (($technology['type'] ?? 'cookie') !== $type) {
                continue;
            }

            if (preg_match($technology['pattern'], $value) !== 1) {
                continue;
            }

            return [
                ...Arr::except($technology, ['type', 'pattern']),
                'technology' => $this->technology($type),
                'pattern' => trim($technology['pattern'], '/'),
            ];
        }

        return null;
    }

    protected function technology(string $type): array
    {
        return [
            'type' => $type,
            'label' => match ($type) {
                'cookie' => 'Cookie',
                'local_storage' => 'Local Storage',
                'session_storage' => 'Session Storage',
                'script' => 'Script',
                default => ucfirst(str_replace('_', ' ', $type)),
            },
        ];
    }

    protected function patterns(): array
    {
        return require __DIR__.'/known-technology-patterns.php';
    }
}
