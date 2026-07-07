<?php

namespace KostantinoAbate\Complihance\Services\Cookies\Scanner\Patterns;

class KnownScriptMatcher
{
    public function match(string $src): ?array
    {
        foreach ($this->patterns() as $script) {
            if (preg_match($script['pattern'], $src) === 1) {
                return [
                    'vendor' => $script['vendor'],
                    'category' => $script['category'],
                    'pattern' => trim($script['pattern'], '/'),
                ];
            }
        }

        return null;
    }

    protected function patterns(): array
    {
        return require __DIR__.'/known-script-patterns.php';
    }
}
