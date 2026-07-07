<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use KostantinoAbate\Complihance\Models\Consent;
use KostantinoAbate\Complihance\Services\Consent\Resolver\CurrentConsentResolver;

uses(RefreshDatabase::class);

beforeEach(function () {
    Config::set('complihance.cookie_name', 'complihance_consent');
    Config::set('complihance.anonymous_cookie_name', 'complihance_anonymous_id');

    Schema::create('users', function (Blueprint $table): void {
        $table->id();
        $table->string('name')->nullable();
        $table->string('email')->unique();
        $table->string('password');
        $table->timestamps();
    });
});

it('falls back to anonymous consent when authenticated user has no consent', function () {
    $userClass = config('auth.providers.users.model');

    $user = $userClass::query()->forceCreate([
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => Hash::make('password'),
    ]);

    $anonymousConsent = Consent::query()->create([
        'anonymous_id' => 'anonymous-123',
        'session_id' => 'session-123',
        'source' => 'banner',
        'accepted_categories' => ['necessary', 'analytics', 'marketing'],
        'rejected_categories' => [],
        'vendors' => ['google-analytics', 'meta-pixel'],
        'policy_version' => '1.0',
        'cookie_configuration_version' => '1.0',
        'accepted_at' => now(),
    ]);

    $request = Request::create('/complihance/preferences', 'GET', [], [
        'complihance_anonymous_id' => 'anonymous-123',
    ]);

    $this->actingAs($user);

    $resolvedConsent = app(CurrentConsentResolver::class)->resolve($request);

    expect($resolvedConsent)->not->toBeNull()
        ->and($resolvedConsent->is($anonymousConsent))->toBeTrue()
        ->and($resolvedConsent->accepted_categories)->toBe([
            'necessary',
            'analytics',
            'marketing',
        ]);
});
