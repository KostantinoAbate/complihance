<?php

namespace KostantinoAbate\Complihance\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use KostantinoAbate\Complihance\Models\Consent;

class ExportConsentsCommand extends Command
{
    protected $signature = 'complihance:export-consents
        {--from= : Start date, e.g. 2026-01-01}
        {--to= : End date, e.g. 2026-12-31}
        {--path= : Export file path}
        {--format=csv : Export format}';

    protected $description = 'Export collected consents for compliance and audit purposes.';

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

        if (! is_dir(dirname($path))) {
            mkdir(dirname($path), 0755, true);
        }

        $handle = fopen($path, 'w');

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
            ->chunkById(500, function ($consents) use ($handle) {
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
                        optional($consent->accepted_at)->toDateTimeString(),
                        optional($consent->revoked_at)->toDateTimeString(),
                        optional($consent->created_at)->toDateTimeString(),
                        optional($consent->updated_at)->toDateTimeString(),
                    ]);
                }
            });

        fclose($handle);

        $this->components->info("Consents exported to: {$path}");

        return self::SUCCESS;
    }
}
