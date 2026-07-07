<?php

namespace KostantinoAbate\Complihance\Commands;

use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Builder;
use KostantinoAbate\Complihance\Models\ComplihancePolicyAcceptance;
use KostantinoAbate\Complihance\Models\Consent;

class RetentionCommand extends Command
{
    protected $signature = 'complihance:retention
        {--dry-run : Show how many records would be processed without changing anything}
        {--action= : Override configured retention action. Supported: anonymize, delete}
        {--only= : Process only a specific resource. Supported: consents, policy-acceptances}
        {--chunk= : Override configured chunk size}
        {--force : Required when using delete action}';

    protected $description = 'Apply Complihance retention rules.';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        if (! config('complihance.retention.enabled', true)) {
            $this->components->warn('Complihance retention is disabled.');

            return self::SUCCESS;
        }

        $action = $this->option('action') ?: config('complihance.retention.expired_action', 'anonymize');
        $chunkSize = (int) ($this->option('chunk') ?: config('complihance.retention.chunk_size', 100));
        $only = $this->option('only');
        $dryRun = (bool) $this->option('dry-run');

        if (! in_array($action, ['anonymize', 'delete'], true)) {
            $this->components->error("Invalid retention action [$action]. Supported: anonymize, delete.");

            return self::FAILURE;
        }

        if ($only !== null && ! in_array($only, ['consents', 'policy-acceptances'], true)) {
            $this->components->error("Invalid resource [$only]. Supported: consents, policy-acceptances.");

            return self::FAILURE;
        }

        if ($chunkSize < 1) {
            $this->components->error('Chunk size must be greater than zero.');

            return self::FAILURE;
        }

        if ($action === 'delete' && ! $this->option('force')) {
            $this->components->error('The delete action is destructive. Please re-run the command with --force.');

            return self::FAILURE;
        }

        $this->components->info($dryRun
            ? 'Running retention in dry-run mode.'
            : 'Running retention.'
        );

        $processedConsents = 0;
        $processedPolicyAcceptances = 0;

        if ($only === null || $only === 'consents') {
            $processedConsents = $this->processExpired(
                Consent::query()->expired(),
                $action,
                $chunkSize,
                $dryRun
            );
        }

        if ($only === null || $only === 'policy-acceptances') {
            $processedPolicyAcceptances = $this->processExpired(
                ComplihancePolicyAcceptance::query()->expired(),
                $action,
                $chunkSize,
                $dryRun
            );
        }

        $this->components->info('Retention completed.');
        $this->line("Action: $action");
        $this->line('Dry run: '.($dryRun ? 'yes' : 'no'));
        $this->line("Expired consents processed: $processedConsents");
        $this->line("Expired policy acceptances processed: $processedPolicyAcceptances");

        return self::SUCCESS;
    }

    /**
     * Process expired records using the selected retention action.
     */
    protected function processExpired(Builder $query, string $action, int $chunkSize, bool $dryRun): int
    {
        if ($dryRun) {
            return (clone $query)->count();
        }

        $processed = 0;

        $query->chunkById($chunkSize, function ($records) use ($action, &$processed): void {
            foreach ($records as $record) {
                if ($action === 'delete') {
                    $record->delete();
                } elseif ($action === 'anonymize') {
                    $record->anonymizeForRetention();
                }

                $processed++;
            }
        });

        return $processed;
    }
}
