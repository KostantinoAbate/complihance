<?php

namespace KostantinoAbate\Complihance\Services\Cookies\Scanner;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Symfony\Component\Process\Process;

class BrowserScanner
{
    public function scan(
        array $urls,
        bool $acceptConsent = true,
        ?string $setupScript = null,
    ): array {
        $scriptPath = $this->createTemporaryScript($urls, $acceptConsent, $setupScript);

        try {
            $process = new Process(['node', $scriptPath]);
            $process->setTimeout(120);
            $process->run();

            if (! $process->isSuccessful()) {
                throw new \RuntimeException(
                    "Browser cookie scanning failed.\n\n".
                    "Make sure Node.js, Playwright, Chromium and the required system dependencies are installed.\n\n".
                    "Install Playwright in your Laravel application:\n".
                    "npm install -D playwright\n".
                    "npx playwright install chromium\n\n".
                    "In Docker/Linux environments, make sure Chromium system dependencies are installed in the image.\n\n".
                    "Alternatively, run the HTTP-only scanner:\n".
                    "php artisan complihance:scan-cookies <url> --http-header-only\n\n".
                    "Exit code: {$process->getExitCode()}\n\n".
                    "STDERR:\n".$process->getErrorOutput()."\n\n".
                    "STDOUT:\n".$process->getOutput()
                );
            }

            $result = json_decode($process->getOutput(), true);

            if (! is_array($result)) {
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

    protected function createTemporaryScript(
        array $urls,
        bool $acceptConsent,
        ?string $setupScript = null,
    ): string {
        $path = storage_path('framework/cache/complihance-cookie-scan-'.Str::uuid().'.mjs');

        File::ensureDirectoryExists(dirname($path));

        File::put($path, $this->script($urls, $acceptConsent, $setupScript));

        return $path;
    }

    protected function script(
        array $urls,
        bool $acceptConsent,
        ?string $setupScript = null,
    ): string {
        $encodedSetupScript = json_encode($setupScript, JSON_THROW_ON_ERROR);
        $encodedUrls = json_encode(array_values($urls), JSON_THROW_ON_ERROR);
        $acceptConsentValue = $acceptConsent ? 'true' : 'false';

        return <<<JS
import { chromium } from 'playwright';

const urls = {$encodedUrls};
const acceptConsent = {$acceptConsentValue};
const setupScriptPath = {$encodedSetupScript};

const browser = await chromium.launch({
    headless: true,
    args: ['--no-sandbox', '--disable-setuid-sandbox'],
});

const context = await browser.newContext();
const page = await context.newPage();
if (setupScriptPath) {
    try {
        const setupModule = await import(setupScriptPath);
        const setup = setupModule.default || setupModule.setup;

        if (typeof setup !== 'function') {
            throw new Error('Setup script must export a default function or a named setup function.');
        }

        await setup({ page, context, browser });
    } catch (error) {
        console.error('Failed to execute setup script.');
        console.error(error.message);
        process.exit(3);
    }
}
const scannedCookies = new Map();
const scannedStorageItems = new Map();
const scannedScripts = new Map();

function cookieKey(cookie) {
    return `\${cookie.name}|\${cookie.domain || ''}|\${cookie.path || '/'}`;
}

function storageKey(item) {
    return `\${item.type}|\${item.key}|\${item.url}`;
}

function scriptKey(script) {
    return `\${script.src}|\${script.url}`;
}

function normalizeCookie(cookie, url) {
    return {
        name: cookie.name,
        domain: cookie.domain || null,
        path: cookie.path || '/',
        url,
        secure: Boolean(cookie.secure),
        http_only: Boolean(cookie.httpOnly),
        same_site: cookie.sameSite || null,
        expires_at: cookie.expires && cookie.expires > 0
            ? new Date(cookie.expires * 1000).toISOString()
            : null,
    };
}

function normalizeStorageItem(type, key, value, url) {
    return {
        type,
        key,
        value_preview: String(value).slice(0, 200),
        url,
    };
}

function normalizeScript(src, url) {
    return {
        type: 'script',
        src,
        url,
    };
}

async function collectStorage(page, url) {
    const storageItems = await page.evaluate(() => {
        const localStorageItems = Object.entries(window.localStorage).map(([key, value]) => ({
            type: 'local_storage',
            key,
            value,
        }));

        const sessionStorageItems = Object.entries(window.sessionStorage).map(([key, value]) => ({
            type: 'session_storage',
            key,
            value,
        }));

        return [...localStorageItems, ...sessionStorageItems];
    });

    storageItems.forEach((item) => {
        const normalized = normalizeStorageItem(item.type, item.key, item.value, url);
        const key = storageKey(normalized);

        if (! scannedStorageItems.has(key)) {
            scannedStorageItems.set(key, normalized);
        }
    });
}

async function collectScripts(page, url) {
    const scripts = await page.evaluate(() =>
        Array.from(document.scripts)
            .map((script) => script.src)
            .filter(Boolean)
    );

    scripts.forEach((src) => {
        const normalized = normalizeScript(src, url);
        const key = scriptKey(normalized);

        if (! scannedScripts.has(key)) {
            scannedScripts.set(key, normalized);
        }
    });
}

async function acceptComplihanceConsent(page) {
    const selectors = [
        '[data-complihance-accept-all]',
        '[data-complihance-action="accept-all"]',
        'button[name="complihance_accept_all"]',
        '#complihance-accept-all',
    ];

    for (const selector of selectors) {
        const element = page.locator(selector).first();

        if (await element.count() > 0) {
            try {
                await element.click({ timeout: 3000 });
                await page.waitForTimeout(1500);
                return true;
            } catch (error) {
                // Try next selector
            }
        }
    }

    return false;
}

for (const url of urls) {
    try {
        await page.goto(url, {
            waitUntil: 'networkidle',
            timeout: 30000,
        });
    } catch (error) {
        console.error('Failed to scan URL: ' + url);
        console.error(error.message);
        process.exit(2);
    }

    await page.waitForTimeout(1000);

    if (acceptConsent) {
        await acceptComplihanceConsent(page);
        await page.waitForTimeout(2000);
    }

    const cookies = await context.cookies();

    cookies.forEach((cookie) => {
        const key = cookieKey(cookie);

        if (! scannedCookies.has(key)) {
            scannedCookies.set(key, normalizeCookie(cookie, url));
        }
    });

    await collectStorage(page, url);
    await collectScripts(page, url);
}

await browser.close();

console.log(JSON.stringify({
    cookies: Array.from(scannedCookies.values()),
    storage: Array.from(scannedStorageItems.values()),
    scripts: Array.from(scannedScripts.values()),
}));
JS;
    }
}
