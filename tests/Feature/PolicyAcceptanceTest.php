<?php

use Illuminate\Foundation\Auth\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use KostantinoAbate\Complihance\Models\ComplihancePolicyAcceptance;
use KostantinoAbate\Complihance\Models\Consent;

uses(RefreshDatabase::class);

function responseCookieValue($response, string $name): string
{
    foreach ($response->headers->getCookies() as $cookie) {
        if ($cookie->getName() === $name) {
            return urldecode($cookie->getValue());
        }
    }

    throw new RuntimeException("Cookie [{$name}] not found.");
}

beforeEach(function () {
    Config::set('complihance.cookie_name', 'complihance_consent');
    Config::set('complihance.anonymous_cookie_name', 'complihance_anonymous_id');
    Config::set('complihance.cookie_configuration_version', '1.0.0');
    Config::set('complihance.granular_consent.enabled', false);

    Config::set('complihance.policies.cookie.driver', 'blade');
    Config::set('complihance.policies.cookie.version', '2026-06-30');

    Config::set('complihance.data.categories_path', __DIR__.'/../Fixtures/categories.json');
    Config::set('complihance.data.technologies_path', __DIR__.'/../Fixtures/cookies.json');
});

it('stores guest policy acceptance with anonymous id', function () {
    $this->postJson('/complihance/api/consent', [
        'categories' => ['analytics'],
        'source' => 'banner',
    ])->assertCreated();

    $consent = Consent::query()->first();
    $acceptance = ComplihancePolicyAcceptance::query()->first();

    expect($acceptance)->not->toBeNull()
        ->and($acceptance->consent_id)->toBe($consent->id)
        ->and($acceptance->anonymous_id)->toBe($consent->anonymous_id)
        ->and($acceptance->policy_key)->toBe('cookie')
        ->and($acceptance->policy_version)->toBe('2026-06-30')
        ->and($acceptance->source)->toBe('banner')
        ->and($acceptance->metadata['accepted_categories'])->toBe([
            'analytics',
            'necessary',
        ])
        ->and($acceptance->metadata['rejected_categories'])->toBe([
            'marketing',
            'functional',
        ]);
});

it('updates authenticated user consent without unique constraint error', function () {
    $user = new class extends User
    {
        protected $table = 'users';

        protected $guarded = [];
    };

    $user->forceFill([
        'id' => 1,
        'name' => 'Test User',
        'email' => 'test@example.com',
    ]);

    $this->actingAs($user);

    $first = $this->postJson('/complihance/api/consent', [
        'categories' => ['analytics'],
        'source' => 'banner',
    ]);

    $first->assertCreated();

    $consentCookie = responseCookieValue($first->baseResponse, 'complihance_consent');

    $this
        ->withCookie('complihance_consent', $consentCookie)
        ->patchJson('/complihance/api/consent', [
            'categories' => ['marketing'],
            'source' => 'preferences',
        ])
        ->assertOk();

    expect(Consent::query()->count())->toBe(2)
        ->and(Consent::query()->whereNull('revoked_at')->count())->toBe(1)
        ->and(ComplihancePolicyAcceptance::query()->count())->toBe(2);

    $acceptance = ComplihancePolicyAcceptance::query()
        ->where('source', 'preferences')
        ->first();

    expect($acceptance)->not->toBeNull()
        ->and($acceptance->subject_type)->toBe($user->getMorphClass())
        ->and($acceptance->subject_id)->toBe($user->getKey())
        ->and($acceptance->source)->toBe('preferences');
});

it('keeps consent audit history when preferences change', function () {
    $first = $this->postJson('/complihance/api/consent', [
        'categories' => ['analytics'],
        'source' => 'banner',
    ]);

    $first->assertCreated();

    $consentCookie = responseCookieValue($first->baseResponse, 'complihance_consent');
    $anonymousCookie = responseCookieValue($first->baseResponse, 'complihance_anonymous_id');

    $this
        ->withCookie('complihance_consent', $consentCookie)
        ->withCookie('complihance_anonymous_id', $anonymousCookie)
        ->patchJson('/complihance/api/consent', [
            'categories' => ['marketing'],
            'source' => 'preferences',
        ])
        ->assertOk();

    expect(Consent::query()->count())->toBe(2);

    expect(
        Consent::query()
            ->whereJsonContains('accepted_categories', 'analytics')
            ->exists()
    )->toBeTrue();

    expect(
        Consent::query()
            ->whereJsonContains('accepted_categories', 'marketing')
            ->exists()
    )->toBeTrue();
});
