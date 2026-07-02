<?php

namespace KostantinoAbate\Complihance\Services;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Symfony\Component\Process\Process;

class BrowserCookieScanner
{
    public function scan(array $urls, bool $acceptConsent = true): array
    {
        $scriptPath = $this->createTemporaryScript($urls, $acceptConsent);

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

            $cookies = json_decode($process->getOutput(), true);

            if (! is_array($cookies)) {
                return [];
            }

            return $cookies;
        } finally {
            File::delete($scriptPath);
        }
    }

    protected function createTemporaryScript(array $urls, bool $acceptConsent): string
    {
        $path = storage_path('framework/cache/complihance-cookie-scan-'.Str::uuid().'.mjs');

        File::ensureDirectoryExists(dirname($path));

        File::put($path, $this->script($urls, $acceptConsent));

        return $path;
    }

    protected function script(array $urls, bool $acceptConsent): string
    {
        $encodedUrls = json_encode(array_values($urls), JSON_THROW_ON_ERROR);
        $acceptConsentValue = $acceptConsent ? 'true' : 'false';

        return <<<JS
import { chromium } from 'playwright';

const urls = {$encodedUrls};
const acceptConsent = {$acceptConsentValue};

const browser = await chromium.launch({
    headless: true,
    args: ['--no-sandbox', '--disable-setuid-sandbox'],
});

const context = await browser.newContext();
const page = await context.newPage();
const scannedCookies = new Map();

function cookieKey(cookie) {
    return `\${cookie.name}|\${cookie.domain || ''}|\${cookie.path || '/'}`;
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
}

await browser.close();

console.log(JSON.stringify(Array.from(scannedCookies.values())));
JS;
    }
}
