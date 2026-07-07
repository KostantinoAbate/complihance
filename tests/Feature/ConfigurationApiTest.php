<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;

uses(RefreshDatabase::class);

beforeEach(function () {
    Config::set('complihance.cookie_configuration_version', '1.0.0');
    Config::set('complihance.policies.cookie.version', '2026-06-30');
    Config::set('complihance.granular_consent.enabled', true);
    Config::set('complihance.consent_mode.enabled', true);

    Config::set('complihance.data.categories_path', __DIR__.'/../Fixtures/categories.json');
    Config::set('complihance.data.technologies_path', __DIR__.'/../Fixtures/granular-cookies.json');
});

it('returns configuration categories as array with key', function () {
    $this
        ->getJson('/complihance/api/configuration')
        ->assertOk()
        ->assertJsonPath('categories.0.key', 'necessary')
        ->assertJsonPath('categories.1.key', 'analytics')
        ->assertJsonPath('categories.2.key', 'marketing')
        ->assertJsonPath('categories.3.key', 'functional')
        ->assertJsonPath('categories.0.required', true);
});

it('returns configuration vendors as array with key', function () {
    $this
        ->getJson('/complihance/api/configuration')
        ->assertOk()
        ->assertJsonPath('vendors.0.key', 'google_analytics')
        ->assertJsonPath('vendors.0.category', 'analytics')
        ->assertJsonPath('vendors.1.key', 'meta_pixel')
        ->assertJsonPath('vendors.1.category', 'marketing');
});

it('returns policy, cookies, granular consent and consent mode metadata', function () {
    $this
        ->getJson('/complihance/api/configuration')
        ->assertOk()
        ->assertJsonPath('granular_consent.enabled', true)
        ->assertJsonPath('consent_mode.enabled', true)
        ->assertJsonPath('policy.version', '2026-06-30')
        ->assertJsonPath('cookies.version', '1.0.0');
});
