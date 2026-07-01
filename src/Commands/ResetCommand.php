<?php

namespace KostantinoAbate\Complihance\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ResetCommand extends Command
{
    protected $signature = 'complihance:reset {--force : Run without confirmation}';

    protected $description = 'Reset Complihance data for local development';

    public function handle(): int
    {
        if (! app()->environment(['local', 'testing'])) {
            $this->components->error('This command can only be executed in local or testing environments.');

            return self::FAILURE;
        }

        if (! $this->option('force') && ! $this->confirm('Delete all Complihance data?', false)) {
            $this->components->warn('Operation cancelled.');

            return self::SUCCESS;
        }

        DB::transaction(function () {
            DB::table('complihance_policy_acceptances')->delete();
            DB::table('complihance_consents')->delete();
        });

        $this->components->info('Complihance data reset completed.');

        $this->newLine();

        $this->line('Current configured versions:');
        $this->line('- Cookie configuration version: ' . config('complihance.cookie_configuration_version'));
        $this->line('- Cookie policy version: ' . config('complihance.policies.cookie.version'));

        return self::SUCCESS;
    }
}
