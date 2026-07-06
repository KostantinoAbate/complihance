<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use KostantinoAbate\Complihance\Facades\ComplihancePolicy;
use KostantinoAbate\Complihance\Models\ComplihancePolicyAcceptance;
use KostantinoAbate\Complihance\Models\Consent;
use KostantinoAbate\Complihance\Services\Policies\PolicyAcceptanceRecorder;

uses(RefreshDatabase::class);

beforeEach(function () {
    Config::set('complihance.cookie_name', 'complihance_consent');
    Config::set('complihance.anonymous_cookie_name', 'complihance_anonymous_id');
    Config::set('complihance.cookie_configuration_version', '1.0.0');
    Config::set('complihance.granular_consent.enabled', false);

    Config::set('complihance.data.categories_path', __DIR__.'/../Fixtures/categories.json');
    Config::set('complihance.data.cookies_path', __DIR__.'/../Fixtures/cookies.json');

    ComplihancePolicy::shouldReceive('currentVersion')
        ->andReturn('2026-06-30');
    $this->app->instance(
        PolicyAcceptanceRecorder::class,
        Mockery::mock(PolicyAcceptanceRecorder::class, function ($mock) {
            $mock->shouldReceive('record')
                ->andReturnUsing(function (
                    string $key,
                    $context,
                    ?Consent $consent = null,
                    ?string $source = null,
                    array $metadata = [],
                ) {
                    return ComplihancePolicyAcceptance::query()->create([
                        'identity_hash' => sha1(uniqid('', true)),
                        'consent_id' => $consent?->id,
                        'subject_type' => $context->subjectType,
                        'subject_id' => $context->subjectId,
                        'session_id' => $context->sessionId,
                        'anonymous_id' => $context->anonymousId,
                        'policy_key' => $key,
                        'policy_version' => '2026-06-30',
                        'source' => $source,
                        'metadata' => $metadata,
                        'ip_address' => $context->ipAddress,
                        'user_agent' => $context->userAgent,
                        'accepted_at' => now(),
                    ]);
                });
        })
    );
});

it('stores guest consent with required category, rejected categories, cookies and database record', function () {
    $response = $this->postJson('/complihance/api/consent', [
        'categories' => ['analytics'],
        'source' => 'banner',
    ]);

    $response
        ->assertCreated()
        ->assertCookie('complihance_consent')
        ->assertCookie('complihance_anonymous_id')
        ->assertJsonPath('has_consent', true)
        ->assertJsonPath('requires_renewal', false)
        ->assertJsonPath('consent.accepted_categories', [
            'analytics',
            'necessary',
        ]);

    expect(Consent::query()->count())->toBe(1);

    $consent = Consent::query()->first();

    expect($consent->accepted_categories)->toBe([
        'analytics',
        'necessary',
    ]);

    expect($consent->rejected_categories)->toBe([
        'marketing',
        'functional',
    ]);

    expect($consent->anonymous_id)->not->toBeNull()
        ->and($consent->session_id)->not->toBeNull()
        ->and($consent->source)->toBe('banner')
        ->and($consent->policy_version)->toBe('2026-06-30')
        ->and($consent->cookie_configuration_version)->toBe('1.0.0')
        ->and($consent->accepted_at)->not->toBeNull()
        ->and($consent->revoked_at)->toBeNull();
});
