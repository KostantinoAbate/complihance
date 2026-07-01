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
                    "Browser cookie scanning requires Playwright, Chromium and the required system dependencies.\n\n".
                    "Install Playwright in your Laravel application:\n".
                    "npm install -D playwright\n".
                    "npx playwright install chromium\n\n".
                    "In Docker/Linux environments, make sure Chromium system dependencies are installed in the image.\n\n".
                    "Alternatively, run the HTTP-only scanner:\n".
                    'php artisan complihance:scan-cookies <url> --http-header-only'
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
    await page.goto(url, {
        waitUntil: 'networkidle',
        timeout: 30000,
    });

    await page.waitForTimeout(1000);

    if (acceptConsent) {
        await acceptComplihanceConsent(page);
        await page.waitForTimeout(2000);
    }
}

const cookies = await context.cookies();

await browser.close();

console.log(JSON.stringify(cookies.map(cookie => ({
    name: cookie.name,
    domain: cookie.domain || null,
    path: cookie.path || '/',
    url: urls[0] || null,
    secure: Boolean(cookie.secure),
    http_only: Boolean(cookie.httpOnly),
    same_site: cookie.sameSite || null,
    expires_at: cookie.expires && cookie.expires > 0
        ? new Date(cookie.expires * 1000).toISOString()
        : null,
}))));
JS;
    }
}
