<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use KostantinoAbate\Complihance\Models\Consent;
use KostantinoAbate\Complihance\Services\Rendering\Resolver\PreferencesVisibilityResolver;

uses(RefreshDatabase::class);

it('does not show preferences when consent cookie is missing', function () {
    Config::set('complihance.banner.enabled', true);
    Config::set('complihance.cookie_name', 'complihance_consent');

    expect(app(PreferencesVisibilityResolver::class)->shouldShow())->toBeFalse();
});

it('does not show preferences when cookie points to revoked consent', function () {
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
    ]));

    expect(app(PreferencesVisibilityResolver::class)->shouldShow())->toBeFalse();
});

it('shows preferences when cookie points to active consent', function () {
    Config::set('complihance.banner.enabled', true);
    Config::set('complihance.cookie_name', 'complihance_consent');

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
    ]));

    expect(app(PreferencesVisibilityResolver::class)->shouldShow())->toBeTrue();
});
