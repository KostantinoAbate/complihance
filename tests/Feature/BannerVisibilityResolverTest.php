<?php

use Illuminate\Support\Facades\Config;
use KostantinoAbate\Complihance\Models\Consent;
use KostantinoAbate\Complihance\Services\BannerVisibilityResolver;
use KostantinoAbate\Complihance\Facades\ComplihancePolicy;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

it('shows banner when consent cookie is missing', function () {
    Config::set('complihance.banner.enabled', true);
    Config::set('complihance.cookie_name', 'complihance_consent');

    expect(app(BannerVisibilityResolver::class)->shouldShow())->toBeTrue();
});

it('shows banner when cookie points to revoked consent', function () {
    Config::set('complihance.banner.enabled', true);
    Config::set('complihance.cookie_name', 'complihance_consent');

    $consent = Consent::query()->create([
        'consent_uuid' => 'revoked-consent-uuid',
        'session_id' => session()->getId(),
        'accepted_categories' => ['necessary'],
        'rejected_categories' => [],
        'vendors' => [],
        'accepted_at' => now(),
        'revoked_at' => now(),
    ]);

    request()->cookies->set('complihance_consent', json_encode([
        'consent_uuid' => $consent->consent_uuid,
        'cookie_configuration_version' => config('complihance.cookie_configuration_version'),
    ]));

    expect(app(BannerVisibilityResolver::class)->shouldShow())->toBeTrue();
});

it('does not show banner when cookie points to active valid consent', function () {
    Config::set('complihance.banner.enabled', true);
    Config::set('complihance.cookie_name', 'complihance_consent');
    Config::set('complihance.cookie_configuration_version', 'v1');

    ComplihancePolicy::shouldReceive('requiresAcceptance')
        ->once()
        ->with('cookie')
        ->andReturnFalse();

    $consent = Consent::query()->create([
        'consent_uuid' => 'active-consent-uuid',
        'session_id' => session()->getId(),
        'accepted_categories' => ['necessary'],
        'rejected_categories' => [],
        'vendors' => [],
        'accepted_at' => now(),
        'revoked_at' => null,
    ]);

    request()->cookies->set('complihance_consent', json_encode([
        'consent_uuid' => $consent->consent_uuid,
        'cookie_configuration_version' => 'v1',
    ]));

    expect(app(BannerVisibilityResolver::class)->shouldShow())->toBeFalse();
});
