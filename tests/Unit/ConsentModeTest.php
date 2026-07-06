<?php

use Illuminate\Support\Facades\Config;
use KostantinoAbate\Complihance\Services\Rendering\ComplihanceDataRepository;
use KostantinoAbate\Complihance\Services\Rendering\ConsentModeService;

beforeEach(function () {
    Config::set('complihance.data.categories_path', __DIR__ . '/../Fixtures/categories.json');

    Config::set('complihance.consent_mode.default', [
        'ad_storage' => 'denied',
        'analytics_storage' => 'denied',
        'ad_user_data' => 'denied',
        'ad_personalization' => 'denied',
        'functionality_storage' => 'denied',
        'personalization_storage' => 'denied',
        'security_storage' => 'granted',
        'unknown_storage' => 'denied',
    ]);
});

function consentMode(): ConsentModeService
{
    return new ConsentModeService(app(ComplihanceDataRepository::class));
}

it('builds consent mode from accepted categories list', function () {
    $payload = consentMode()->fromCategories(['analytics']);

    expect($payload['analytics_storage'])->toBe('granted')
        ->and($payload['ad_storage'])->toBe('denied')
        ->and($payload['ad_user_data'])->toBe('denied')
        ->and($payload['ad_personalization'])->toBe('denied')
        ->and($payload['functionality_storage'])->toBe('denied')
        ->and($payload['personalization_storage'])->toBe('denied')
        ->and($payload['security_storage'])->toBe('granted');
});

it('builds consent mode from accepted categories map', function () {
    $payload = consentMode()->fromCategories([
        'analytics' => true,
        'marketing' => true,
        'functional' => false,
    ]);

    expect($payload['analytics_storage'])->toBe('granted')
        ->and($payload['ad_storage'])->toBe('granted')
        ->and($payload['ad_user_data'])->toBe('granted')
        ->and($payload['ad_personalization'])->toBe('granted')
        ->and($payload['functionality_storage'])->toBe('denied')
        ->and($payload['personalization_storage'])->toBe('denied')
        ->and($payload['security_storage'])->toBe('granted');
});

it('keeps not configured consent mode keys denied', function () {
    $payload = consentMode()->fromCategories(['analytics', 'not_configured']);

    expect($payload['analytics_storage'])->toBe('granted')
        ->and($payload['unknown_storage'])->toBe('denied');
});
