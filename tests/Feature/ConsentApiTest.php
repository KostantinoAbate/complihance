<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Route;
use KostantinoAbate\Complihance\Http\Controllers\Api\ConsentApiController;
use KostantinoAbate\Complihance\Models\Consent;

it('revokes consent and forgets consent cookies', function () {
    Config::set('complihance.cookie_name', 'complihance_consent');
    Config::set('complihance.anonymous_cookie_name', 'complihance_anonymous_id');

    $consent = Consent::query()->create([
        'consent_uuid' => 'test-consent-uuid',
        'session_id' => session()->getId(),
        'accepted_categories' => ['necessary', 'analytics'],
        'rejected_categories' => [],
        'vendors' => [],
        'accepted_at' => now(),
        'revoked_at' => null,
    ]);

    Route::delete('/test-complihance/revoke', function (Request $request) use ($consent) {
        $controller = new class($consent) extends ConsentApiController {
            public function __construct(private Consent $testConsent) {}

            protected function currentConsent(Request $request): ?Consent
            {
                return $this->testConsent;
            }
        };

        return $controller->revoke($request);
    });

    $this
        ->withCookie('complihance_consent', 'stale-cookie')
        ->withCookie('complihance_anonymous_id', 'anonymous-id')
        ->deleteJson('/test-complihance/revoke')
        ->assertOk()
        ->assertJson([
            'revoked' => true,
            'has_consent' => false,
            'requires_renewal' => true,
            'consent' => null,
        ])
        ->assertCookieExpired('complihance_consent')
        ->assertCookieExpired('complihance_anonymous_id');

    expect($consent->fresh()->revoked_at)->not->toBeNull();
});
