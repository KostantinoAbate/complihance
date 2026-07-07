<?php

namespace KostantinoAbate\Complihance\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use KostantinoAbate\Complihance\Models\Consent;
use RuntimeException;

class ExportConsentsCommand extends Command
{
    protected $signature = 'complihance:export-consents
        {--from= : Start date, e.g. 2026-01-01}
        {--to= : End date, e.g. 2026-12-31}
        {--path= : Export file path}
        {--format=csv : Export format}';

    protected $description = 'Export collected consents for compliance and audit purposes.';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        if ($this->option('format') !== 'csv') {
            $this->components->error('Only CSV export is currently supported.');

            return self::FAILURE;
        }

        $from = $this->option('from')
            ? Carbon::parse($this->option('from'))->startOfDay()
            : null;

        $to = $this->option('to')
            ? Carbon::parse($this->option('to'))->endOfDay()
            : null;

        $path = $this->option('path')
            ?: storage_path('app/complihance/exports/consents-'.now()->format('Y-m-d-His').'.csv');

        $directory = dirname($path);

        if (! is_dir($directory) && ! mkdir($directory, 0755, true) && ! is_dir($directory)) {
            throw new RuntimeException("Unable to create export directory: $directory");
        }

        $handle = fopen($path, 'w');

        if ($handle === false) {
            throw new RuntimeException("Unable to open export file for writing: $path");
        }

        fputcsv($handle, [
            'id',
            'consent_uuid',
            'subject_type',
            'subject_id',
            'session_id',
            'anonymous_id',
            'source',
            'accepted_categories',
            'rejected_categories',
            'vendors',
            'policy_version',
            'cookie_configuration_version',
            'ip_address',
            'user_agent',
            'accepted_at',
            'revoked_at',
            'created_at',
            'updated_at',
        ]);

        Consent::query()
            ->when($from, fn ($query) => $query->where('accepted_at', '>=', $from))
            ->when($to, fn ($query) => $query->where('accepted_at', '<=', $to))
            ->orderBy('id')
            ->chunkById(500, function ($consents) use ($handle): void {
                foreach ($consents as $consent) {
                    fputcsv($handle, [
                        $consent->id,
                        $consent->consent_uuid,
                        $consent->subject_type,
                        $consent->subject_id,
                        $consent->session_id,
                        $consent->anonymous_id,
                        $consent->source,
                        json_encode($consent->accepted_categories ?? [], JSON_UNESCAPED_SLASHES),
                        json_encode($consent->rejected_categories ?? [], JSON_UNESCAPED_SLASHES),
                        json_encode($consent->vendors ?? [], JSON_UNESCAPED_SLASHES),
                        $consent->policy_version,
                        $consent->cookie_configuration_version,
                        $consent->ip_address,
                        $consent->user_agent,
                        $consent->accepted_at?->toDateTimeString(),
                        $consent->revoked_at?->toDateTimeString(),
                        $consent->created_at?->toDateTimeString(),
                        $consent->updated_at?->toDateTimeString(),
                    ]);
                }
            });

        fclose($handle);

        $this->components->info("Consents exported to: $path");

        return self::SUCCESS;
    }
}
