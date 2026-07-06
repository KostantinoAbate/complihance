<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use KostantinoAbate\Complihance\Models\Consent;
use KostantinoAbate\Complihance\Services\Consent\Resolver\CurrentConsentResolver;

uses(RefreshDatabase::class);

beforeEach(function () {
    Config::set('complihance.cookie_name', 'complihance_consent');
    Config::set('complihance.anonymous_cookie_name', 'complihance_anonymous_id');
});

it('does not inherit anonymous consent when request has no session and no anonymous cookie', function () {
    Consent::query()->create([
        'consent_uuid' => 'first-anonymous-consent',
        'session_id' => null,
        'anonymous_id' => 'anonymous-one',
        'source' => 'banner',
        'accepted_categories' => ['necessary', 'analytics'],
        'rejected_categories' => ['marketing', 'functional'],
        'vendors' => [],
        'policy_version' => '2026-06-30',
        'cookie_configuration_version' => '1.0.0',
        'accepted_at' => now(),
        'revoked_at' => null,
    ]);

    Consent::query()->create([
        'consent_uuid' => 'second-anonymous-consent',
        'session_id' => null,
        'anonymous_id' => 'anonymous-two',
        'source' => 'banner',
        'accepted_categories' => ['necessary', 'marketing'],
        'rejected_categories' => ['analytics', 'functional'],
        'vendors' => [],
        'policy_version' => '2026-06-30',
        'cookie_configuration_version' => '1.0.0',
        'accepted_at' => now(),
        'revoked_at' => null,
    ]);

    $request = Request::create('/complihance/api/consent', 'GET');

    $consent = app(CurrentConsentResolver::class)->resolve($request);

    expect($request->hasSession())->toBeFalse()
        ->and($consent)->toBeNull();
});

it('resolves only the anonymous visitor matching the anonymous cookie without session', function () {
    Consent::query()->create([
        'consent_uuid' => 'first-anonymous-consent',
        'session_id' => null,
        'anonymous_id' => 'anonymous-one',
        'source' => 'banner',
        'accepted_categories' => ['necessary', 'analytics'],
        'rejected_categories' => ['marketing', 'functional'],
        'vendors' => [],
        'policy_version' => '2026-06-30',
        'cookie_configuration_version' => '1.0.0',
        'accepted_at' => now()->subMinute(),
        'revoked_at' => null,
    ]);

    Consent::query()->create([
        'consent_uuid' => 'second-anonymous-consent',
        'session_id' => null,
        'anonymous_id' => 'anonymous-two',
        'source' => 'banner',
        'accepted_categories' => ['necessary', 'marketing'],
        'rejected_categories' => ['analytics', 'functional'],
        'vendors' => [],
        'policy_version' => '2026-06-30',
        'cookie_configuration_version' => '1.0.0',
        'accepted_at' => now(),
        'revoked_at' => null,
    ]);

    $firstRequest = Request::create('/complihance/api/consent', 'GET');
    $firstRequest->cookies->set('complihance_anonymous_id', 'anonymous-one');

    $firstConsent = app(CurrentConsentResolver::class)->resolve($firstRequest);

    expect($firstRequest->hasSession())->toBeFalse()
        ->and($firstConsent?->consent_uuid)->toBe('first-anonymous-consent')
        ->and($firstConsent?->anonymous_id)->toBe('anonymous-one');

    $secondRequest = Request::create('/complihance/api/consent', 'GET');
    $secondRequest->cookies->set('complihance_anonymous_id', 'anonymous-two');

    $secondConsent = app(CurrentConsentResolver::class)->resolve($secondRequest);

    expect($secondRequest->hasSession())->toBeFalse()
        ->and($secondConsent?->consent_uuid)->toBe('second-anonymous-consent')
        ->and($secondConsent?->anonymous_id)->toBe('anonymous-two');
});
