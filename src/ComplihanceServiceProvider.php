<?php

namespace KostantinoAbate\Complihance;

use Illuminate\Support\Facades\Blade;
use KostantinoAbate\Complihance\Commands\ResetCommand;
use KostantinoAbate\Complihance\Commands\RetentionCommand;
use KostantinoAbate\Complihance\Commands\ScanCookiesCommand;
use KostantinoAbate\Complihance\PolicyManagement\PolicyManager;
use KostantinoAbate\Complihance\Services\ComplihanceScriptRenderer;
use KostantinoAbate\Complihance\Services\ConsentModeRenderer;
use KostantinoAbate\Complihance\Support\BlockedContentAttributes;
use KostantinoAbate\Complihance\Support\ComplihanceHtmlSanitizer;
use KostantinoAbate\Complihance\View\Components\Banner;
use KostantinoAbate\Complihance\View\Components\CookieTable;
use KostantinoAbate\Complihance\View\Components\Preferences;
use Spatie\LaravelPackageTools\Commands\InstallCommand;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class ComplihanceServiceProvider extends PackageServiceProvider
{
    public function packageBooted(): void
    {
        $this->publishes([
            __DIR__.'/../resources/data/categories.json' => resource_path('vendor/complihance/categories.json'),
            __DIR__.'/../resources/data/cookies.json' => resource_path('vendor/complihance/cookies.json'),
            __DIR__.'/../resources/data/texts.json' => resource_path('vendor/complihance/texts.json'),
        ], 'complihance-data');

        Blade::directive('complihanceBanner', function () {
            return "<?php echo Blade::render('<x-complihance-banner />'); ?>";
        });

        Blade::directive('complihancePreferences', function () {
            return "<?php echo Blade::render('<x-complihance-preferences />'); ?>";
        });

        Blade::directive('complihanceScript', function () {
            return '<?php echo app(\\'.ComplihanceScriptRenderer::class.'::class)->render(); ?>';
        });

        Blade::directive('complihanceConsentMode', function () {
            return '<?php echo app(\\'.ConsentModeRenderer::class.'::class)->render(); ?>';
        });

        Blade::directive('complihanceCookieTable', function ($expression) {
            $expression = trim($expression ?: 'null');

            return "<?php echo Blade::render('<x-complihance-cookie-table :category=\"\$category\" />', [
                'category' => {$expression},
            ]); ?>";
        });

        Blade::directive('complihanceBlockedContent', function ($expression) {
            $expression = trim($expression ?: '');

            if ($expression === '') {
                return '<?php echo app(\\'.BlockedContentAttributes::class.'::class)->render(); ?>';
            }

            return '<?php echo app(\\'.BlockedContentAttributes::class."::class)->render({$expression}); ?>";
        });

        Blade::directive('complihanceHtml', function ($expression) {
            return "<?php echo app(".ComplihanceHtmlSanitizer::class."::class)->sanitize($expression); ?>";
        });
    }

    public function packageRegistered(): void
    {
        $this->app->singleton('complihance.policy', function () {
            return new PolicyManager;
        });
        $this->loadRoutesFrom(__DIR__.'/../routes/api.php');
    }

    public function configurePackage(Package $package): void
    {
        /*
         * This class is a Package Service Provider
         *
         * More info: https://github.com/spatie/laravel-package-tools
         */
        $package
            ->name('complihance')
            ->hasConfigFile('complihance')
            ->hasViews()
            ->hasRoute('web')
            ->hasAssets()
            ->discoversMigrations()
            ->hasViewComponents('complihance', Banner::class, Preferences::class, CookieTable::class)
            ->hasTranslations()
            ->hasCommands(RetentionCommand::class, ScanCookiesCommand::class, ResetCommand::class)
            ->hasInstallCommand(function (InstallCommand $command) {
                $command
                    ->publishConfigFile()
                    ->publishAssets()
                    ->publishMigrations()
                    ->copyAndRegisterServiceProviderInApp()
                    ->endWith(function (InstallCommand $command) {
                        $command->info('Thanks for installing Complihance!');

                        $command->newLine();

                        $command->line('Publish editable Complihance data files:');
                        $command->line('php artisan vendor:publish --tag=complihance-data');

                        $command->newLine();

                        $command->warn('Cookie scanner browser mode requires Playwright and Chromium.');
                        $command->line('Install them in your Laravel application if you want to detect JavaScript-generated cookies:');

                        $command->newLine();

                        $command->line('npm install -D playwright');
                        $command->line('npx playwright install chromium');

                        $command->newLine();

                        $command->line('Alternatively, use the HTTP-only scanner mode:');
                        $command->line('php artisan complihance:scan-cookies https://example.com --http-header-only');
                    });
            });
    }
}
