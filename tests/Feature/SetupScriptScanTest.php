<?php

use Illuminate\Support\Facades\File;
use KostantinoAbate\Complihance\Models\CookieScanResult;
use Symfony\Component\Process\Process;

it('runs setup script during browser scan', function () {
    $process = new Process([
        'node',
        '-e',
        "import('playwright').then(() => process.exit(0)).catch(() => process.exit(1))",
    ]);

    $process->run();

    if (! $process->isSuccessful()) {
        $this->markTestSkipped('Playwright is not installed.');
    }

    $setupScript = base_path('tests/Script/test-setup.mjs');

    File::ensureDirectoryExists(dirname($setupScript));

    File::put($setupScript, <<<'JS'
export default async function setup({ page }) {
    await page.addInitScript(() => {
        window.localStorage.setItem('setup_script_ran', 'yes');
    });
}
JS);

    artisan('complihance:scan-cookies', [
        'url' => ['http://webserver/complihance-js-cookie-scan-test'],
        '--setup-script' => $setupScript,
        '--report' => 'json',
    ])->assertSuccessful();

    expect(
        CookieScanResult::query()
            ->where('type', 'local_storage')
            ->where('key', 'setup_script_ran')
            ->exists()
    )->toBeTrue();

    File::delete($setupScript);
});
