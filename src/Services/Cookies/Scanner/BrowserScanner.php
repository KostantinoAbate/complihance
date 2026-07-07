<?php

namespace KostantinoAbate\Complihance\Services\Cookies\Scanner;

use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use JsonException;
use RuntimeException;
use Symfony\Component\Process\Process;

class BrowserScanner
{
    /**
     * Scan URLs with Playwright and collect cookies, storage entries, and scripts.
     *
     * @param array<int, string> $urls
     * @return array{cookies: array<int, array<string, mixed>>, storage: array<int, array<string, mixed>>, scripts: array<int, array<string, mixed>>}
     * @throws JsonException|FileNotFoundException
     */
    public function scan(
        array   $urls,
        bool    $acceptConsent = true,
        ?string $setupScript = null,
    ): array
    {
        $scriptPath = $this->createTemporaryScript($urls, $acceptConsent, $setupScript);

        try {
            $process = new Process(['node', $scriptPath]);
            $process->setTimeout(120);
            $process->run();

            if (!$process->isSuccessful()) {
                throw new RuntimeException(
                    "Browser cookie scanning failed.\n\n" .
                    "Make sure Node.js, Playwright, Chromium and the required system dependencies are installed.\n\n" .
                    "Install Playwright in your Laravel application:\n" .
                    "npm install -D playwright\n" .
                    "npx playwright install chromium\n\n" .
                    "In Docker/Linux environments, make sure Chromium system dependencies are installed in the image.\n\n" .
                    "Alternatively, run the HTTP-only scanner:\n" .
                    "php artisan complihance:scan-cookies <url> --http-header-only\n\n" .
                    "Exit code: {$process->getExitCode()}\n\n" .
                    "STDERR:\n" . $process->getErrorOutput() . "\n\n" .
                    "STDOUT:\n" . $process->getOutput()
                );
            }

            try {
                $result = json_decode($process->getOutput(), true, 512, JSON_THROW_ON_ERROR);
            } catch (JsonException) {
                return [
                    'cookies' => [],
                    'storage' => [],
                    'scripts' => [],
                ];
            }

            if (!is_array($result)) {
                return [
                    'cookies' => [],
                    'storage' => [],
                    'scripts' => [],
                ];
            }

            if (array_is_list($result)) {
                return [
                    'cookies' => $result,
                    'storage' => [],
                    'scripts' => [],
                ];
            }

            return [
                'cookies' => $result['cookies'] ?? [],
                'storage' => $result['storage'] ?? [],
                'scripts' => $result['scripts'] ?? [],
            ];
        } finally {
            File::delete($scriptPath);
        }
    }

    /**
     * Create the temporary Playwright script used for scanning.
     *
     * @param array<int, string> $urls
     * @throws JsonException|FileNotFoundException
     */
    protected function createTemporaryScript(
        array   $urls,
        bool    $acceptConsent,
        ?string $setupScript = null,
    ): string
    {
        $path = storage_path('framework/cache/complihance-cookie-scan-' . Str::uuid() . '.mjs');

        File::ensureDirectoryExists(dirname($path));

        File::put($path, $this->script($urls, $acceptConsent, $setupScript));

        return $path;
    }

    /**
     * Build the Playwright script content.
     *
     * @param array<int, string> $urls
     * @throws JsonException
     * @throws FileNotFoundException
     */
    protected function script(
        array   $urls,
        bool    $acceptConsent,
        ?string $setupScript = null,
    ): string
    {
        $setupScript = $setupScript !== null
            ? realpath($setupScript) ?: $setupScript
            : null;

        $encodedSetupScript = json_encode($setupScript, JSON_THROW_ON_ERROR);
        $encodedUrls = json_encode(array_values($urls), JSON_THROW_ON_ERROR);
        $encodedAcceptConsent = json_encode($acceptConsent, JSON_THROW_ON_ERROR);

        $encodedSetupScriptJson = json_encode($encodedSetupScript, JSON_THROW_ON_ERROR);
        $encodedUrlsJson = json_encode($encodedUrls, JSON_THROW_ON_ERROR);
        $encodedAcceptConsentJson = json_encode($encodedAcceptConsent, JSON_THROW_ON_ERROR);

        $template = File::get(
            dirname(__DIR__, 5) . '/resources/js/scanner/browser-scanner.mjs.stub'
        );

        return strtr($template, [
            '__URLS_JSON__' => $encodedUrlsJson,
            '__ACCEPT_CONSENT_JSON__' => $encodedAcceptConsentJson,
            '__SETUP_SCRIPT_PATH_JSON__' => $encodedSetupScriptJson,
        ]);
    }
}
