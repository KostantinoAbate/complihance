<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Config;
use KostantinoAbate\Complihance\Facades\ComplihancePolicy;
use KostantinoAbate\Complihance\Models\Consent;
use KostantinoAbate\Complihance\Services\Rendering\Resolver\BannerVisibilityResolver;

uses(RefreshDatabase::class);

beforeEach(function () {
    Config::set('complihance.banner.enabled', true);
    Config::set('complihance.cookie_name', 'complihance_consent');
    Config::set('complihance.anonymous_cookie_name', 'complihance_anonymous_id');
    Config::set('complihance.cookie_configuration_version', '1.0.0');
    Config::set('complihance.granular_consent.enabled', false);

    Config::set('complihance.data.categories_path', __DIR__.'/../Fixtures/categories.json');
    Config::set('complihance.data.cookies_path', __DIR__.'/../Fixtures/cookies.json');
});

it('shows banner when cookie policy version changes', function () {
    ComplihancePolicy::shouldReceive('requiresAcceptance')
        ->once()
        ->andReturnTrue();

    $consent = Consent::query()->create([
        'consent_uuid' => 'active-consent-uuid',
        'session_id' => session()->getId(),
        'anonymous_id' => 'anonymous-id',
        'accepted_categories' => ['necessary', 'analytics'],
        'rejected_categories' => ['marketing', 'functional'],
        'vendors' => [],
        'policy_version' => 'old-version',
        'cookie_configuration_version' => '1.0.0',
        'accepted_at' => now(),
        'revoked_at' => null,
    ]);

    request()->cookies->set('complihance_consent', json_encode([
        'consent_uuid' => $consent->consent_uuid,
        'anonymous_id' => 'anonymous-id',
        'cookie_configuration_version' => '1.0.0',
    ]));

    request()->cookies->set('complihance_anonymous_id', 'anonymous-id');

    expect(app(BannerVisibilityResolver::class)->shouldShow())->toBeTrue();
});

it('shows banner when cookie configuration version changes', function () {
    ComplihancePolicy::shouldReceive('requiresAcceptance')
        ->once()
        ->andReturnFalse();

    Config::set('complihance.cookie_configuration_version', '2.0.0');

    $consent = Consent::query()->create([
        'consent_uuid' => 'active-consent-uuid',
        'session_id' => session()->getId(),
        'anonymous_id' => 'anonymous-id',
        'accepted_categories' => ['necessary', 'analytics'],
        'rejected_categories' => ['marketing', 'functional'],
        'vendors' => [],
        'policy_version' => '2026-06-30',
        'cookie_configuration_version' => '1.0.0',
        'accepted_at' => now(),
        'revoked_at' => null,
    ]);

    request()->cookies->set('complihance_consent', json_encode([
        'consent_uuid' => $consent->consent_uuid,
        'anonymous_id' => 'anonymous-id',
        'cookie_configuration_version' => '1.0.0',
    ]));

    request()->cookies->set('complihance_anonymous_id', 'anonymous-id');

    expect(app(BannerVisibilityResolver::class)->shouldShow())->toBeTrue();
});

it('renders banner component and directive consistently', function () {
    ComplihancePolicy::shouldReceive('requiresAcceptance')
        ->zeroOrMoreTimes()
        ->andReturnTrue();

    $componentHtml = Blade::render('<x-complihance-banner />');
    $directiveHtml = Blade::render('@complihanceBanner');

    expect($componentHtml)->not->toBe('')
        ->and($directiveHtml)->not->toBe('')
        ->and($directiveHtml)->toBe($componentHtml);
});
