<?php

namespace KostantinoAbate\Complihance\Services;

class CookieConfigWriter
{
    public function __construct(
        protected KnownCookieMatcher $matcher,
    ) {}

    public function addMissingCookies(array $cookieNames): int
    {
        $path = config_path('complihance-cookies.php');

        if (! file_exists($path)) {
            $this->createConfigFile($path);
        }

        $config = require $path;

        $cookies = $config['cookies'] ?? [];
        $added = 0;

        foreach ($cookieNames as $cookieName) {
            if (array_key_exists($cookieName, $cookies)) {
                continue;
            }

            $cookies[$cookieName] = $this->matcher->match($cookieName) ?? $this->fallbackCookieDefinition();

            $added++;
        }

        if ($added === 0) {
            return 0;
        }

        ksort($cookies);

        $config['cookies'] = $cookies;

        file_put_contents($path, $this->exportConfig($config));

        return $added;
    }

    public function ensureCoreCookies(): int
    {
        return $this->addMissingCookieDefinitions([
            'complihance_consent' => [
                'category' => 'necessary',
                'vendor' => 'Complihance',
                'duration' => '12 months',
                'description' => 'Stores the user cookie consent preferences.',
            ],

            'complihance_anonymous_id' => [
                'category' => 'necessary',
                'vendor' => 'Complihance',
                'duration' => '12 months',
                'description' => 'Stores an anonymous identifier used to associate consent records with anonymous visitors.',
            ],
        ]);
    }

    public function addMissingCookieDefinitions(array $definitions): int
    {
        $path = config_path('complihance-cookies.php');

        if (! file_exists($path)) {
            $this->createConfigFile($path);
        }

        $config = require $path;

        $cookies = $config['cookies'] ?? [];
        $added = 0;

        foreach ($definitions as $cookieName => $definition) {
            if (array_key_exists($cookieName, $cookies)) {
                continue;
            }

            $cookies[$cookieName] = $definition;
            $added++;
        }

        if ($added === 0) {
            return 0;
        }

        ksort($cookies);

        $config['cookies'] = $cookies;

        file_put_contents($path, $this->exportConfig($config));

        return $added;
    }

    protected function createConfigFile(string $path): void
    {
        file_put_contents($path, $this->exportConfig([
            'cookies' => [],
        ]));
    }

    protected function fallbackCookieDefinition(): array
    {
        return [
            'category' => 'unclassified',
            'vendor' => null,
            'duration' => 'Session',
            'description' => null,
        ];
    }

    protected function exportConfig(array $config): string
    {
        return "<?php\n\nreturn [\n"
            .$this->exportArrayItems($config, 1)
            ."];\n";
    }

    protected function exportArrayItems(array $array, int $level): string
    {
        $output = '';

        foreach ($array as $key => $value) {
            $indent = str_repeat('    ', $level);

            $output .= $indent.$this->exportKey($key).' => ';

            if (is_array($value)) {
                $output .= "[\n"
                    .$this->exportArrayItems($value, $level + 1)
                    .$indent.']';
            } else {
                $output .= $this->exportValue($value);
            }

            $output .= ",\n";
        }

        return $output;
    }

    protected function exportKey(string|int $key): string
    {
        return is_int($key)
            ? (string) $key
            : "'".str_replace("'", "\\'", $key)."'";
    }

    protected function exportValue(mixed $value): string
    {
        return match (true) {
            is_null($value) => 'null',
            is_bool($value) => $value ? 'true' : 'false',
            is_int($value), is_float($value) => (string) $value,
            default => "'".str_replace("'", "\\'", (string) $value)."'",
        };
    }
}
