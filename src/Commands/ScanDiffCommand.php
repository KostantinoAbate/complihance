<?php

namespace KostantinoAbate\Complihance\Commands;

use Illuminate\Console\Command;
use JsonException;
use KostantinoAbate\Complihance\Models\CookieScan;
use KostantinoAbate\Complihance\Services\Cookies\Scanner\ScanDiffService;

class ScanDiffCommand extends Command
{
    protected $signature = 'complihance:scan-diff
        {from : Source scan id or uuid}
        {to : Target scan id or uuid}
        {--include-volatile : Include volatile fields like expires_at in changes}
        {--json : Output full diff as JSON}';

    protected $description = 'Compare two Complihance cookie scans.';

    /**
     * Execute the console command.
     * @throws JsonException
     */
    public function handle(ScanDiffService $diffService): int
    {
        $from = $this->findScan((string) $this->argument('from'));
        $to = $this->findScan((string) $this->argument('to'));

        $diff = $diffService->diff(
            $from,
            $to,
            (bool) $this->option('include-volatile')
        );

        if ($this->option('json')) {
            $this->line(json_encode(
                $diff,
                JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
            ));

            return self::SUCCESS;
        }

        $this->components->info("From scan: {$from->getAttribute('uuid')}");
        $this->components->info("To scan: {$to->getAttribute('uuid')}");

        $this->newLine();

        $this->components->twoColumnDetail('Added', (string) $diff['summary']['added']);
        $this->components->twoColumnDetail('Removed', (string) $diff['summary']['removed']);
        $this->components->twoColumnDetail('Changed', (string) $diff['summary']['changed']);
        $this->components->twoColumnDetail('Unchanged', (string) $diff['summary']['unchanged']);

        if ($diff['added'] !== []) {
            $this->newLine();
            $this->components->info('Added');

            foreach ($diff['added'] as $item) {
                $this->line("- [{$item['type']}] {$item['key']}");
            }
        }

        if ($diff['removed'] !== []) {
            $this->newLine();
            $this->components->warn('Removed');

            foreach ($diff['removed'] as $item) {
                $this->line("- [{$item['type']}] {$item['key']}");
            }
        }

        if ($diff['changed'] !== []) {
            $this->newLine();
            $this->components->warn('Changed');

            foreach ($diff['changed'] as $item) {
                $this->line("- [{$item['type']}] {$item['key']}");

                foreach ($item['changes'] as $field => $change) {
                    $this->line(sprintf(
                        '  %s: %s → %s',
                        $field,
                        $this->formatDiffValue($change['from'] ?? null),
                        $this->formatDiffValue($change['to'] ?? null),
                    ));
                }
            }
        }

        return self::SUCCESS;
    }

    /**
     * Find a cookie scan by its numeric id or UUID.
     */
    protected function findScan(string $value): CookieScan
    {
        return CookieScan::query()
            ->where('uuid', $value)
            ->orWhere('id', $value)
            ->firstOrFail();
    }

    /**
     * Format a diff value for console output.
     * @throws JsonException
     */
    protected function formatDiffValue(mixed $value): string
    {
        if ($value === null) {
            return 'null';
        }

        if (is_bool($value)) {
            return $value ? 'true' : 'false';
        }

        if (is_scalar($value)) {
            return (string) $value;
        }

        return json_encode($value, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }
}
