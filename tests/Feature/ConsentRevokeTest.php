<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use KostantinoAbate\Complihance\Actions\Consent\RevokeConsentAction;
use KostantinoAbate\Complihance\Facades\ComplihancePolicy;
use KostantinoAbate\Complihance\Models\Consent;
use KostantinoAbate\Complihance\Services\Rendering\Resolver\BannerVisibilityResolver;

uses(RefreshDatabase::class);

beforeEach(function () {
    Config::set('complihance.banner.enabled', true);
    Config::set('complihance.cookie_name', 'complihance_consent');
    Config::set('complihance.anonymous_cookie_name', 'complihance_anonymous_id');
    Config::set('complihance.cookie_configuration_version', '1.0.0');

    ComplihancePolicy::shouldReceive('requiresAcceptance')
        ->zeroOrMoreTimes()
        ->andReturnFalse();
});

function revokeConsentCookiePayload(Consent $consent): string
{
    return json_encode([
        'consent_uuid' => $consent->consent_uuid,
        'anonymous_id' => $consent->anonymous_id,
        'cookie_configuration_version' => $consent->cookie_configuration_version,
    ]);
}

it('revokes current consent', function () {
    $consent = Consent::query()->create([
        'consent_uuid' => 'consent-to-revoke',
        'session_id' => null,
        'anonymous_id' => 'anonymous-id',
        'source' => 'banner',
        'accepted_categories' => ['necessary', 'analytics'],
        'rejected_categories' => ['marketing', 'functional'],
        'vendors' => [],
        'policy_version' => '2026-06-30',
        'cookie_configuration_version' => '1.0.0',
        'accepted_at' => now(),
        'revoked_at' => null,
    ]);

    $request = Request::create('/complihance/api/consent', 'DELETE');
    $request->cookies->set('complihance_consent', revokeConsentCookiePayload($consent));
    $request->cookies->set('complihance_anonymous_id', 'anonymous-id');

    app(RevokeConsentAction::class)->execute($request);

    expect($consent->fresh()->revoked_at)->not->toBeNull();
});

it('forgets consent cookies through api revoke response', function () {
    $this
        ->deleteJson('/complihance/api/consent')
        ->assertOk()
        ->assertJson([
            'revoked' => true,
            'has_consent' => false,
            'requires_renewal' => true,
            'consent' => null,
        ])
        ->assertCookieExpired('complihance_consent')
        ->assertCookieExpired('complihance_anonymous_id');
});

it('returns no consent after revoked consent and banner becomes visible again', function () {
    $consent = Consent::query()->create([
        'consent_uuid' => 'revoked-consent',
        'session_id' => null,
        'anonymous_id' => 'anonymous-id',
        'source' => 'banner',
        'accepted_categories' => ['necessary', 'analytics'],
        'rejected_categories' => ['marketing', 'functional'],
        'vendors' => [],
        'policy_version' => '2026-06-30',
        'cookie_configuration_version' => '1.0.0',
        'accepted_at' => now(),
        'revoked_at' => now(),
    ]);

    $cookiePayload = revokeConsentCookiePayload($consent);

    $this
        ->withCookie('complihance_consent', $cookiePayload)
        ->withCookie('complihance_anonymous_id', 'anonymous-id')
        ->getJson('/complihance/api/consent')
        ->assertOk()
        ->assertJson([
            'has_consent' => false,
            'requires_renewal' => true,
            'consent' => null,
        ]);

    request()->cookies->set('complihance_consent', $cookiePayload);
    request()->cookies->set('complihance_anonymous_id', 'anonymous-id');

    expect(app(BannerVisibilityResolver::class)->shouldShow())->toBeTrue();
});
