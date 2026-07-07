<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use KostantinoAbate\Complihance\Models\Consent;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

it('exports consents to csv', function () {
    $path = storage_path('app/testing/complihance-consents.csv');

    File::delete($path);

    Consent::query()->create([
        'consent_uuid' => (string) Str::uuid(),
        'source' => 'banner',
        'accepted_categories' => ['necessary', 'analytics'],
        'rejected_categories' => ['marketing'],
        'vendors' => ['google-analytics'],
        'policy_version' => '2026-01-01',
        'cookie_configuration_version' => '2026-01-01',
        'anonymous_id' => 'anon_123',
        'accepted_at' => now(),
    ]);

    $this->artisan('complihance:export-consents', [
        '--path' => $path,
    ])
        ->assertSuccessful();

    expect(File::exists($path))->toBeTrue();

    $csv = File::get($path);

    expect($csv)
        ->toContain('anonymous')
        ->toContain('anon_123')
        ->toContain('google-analytics')
        ->toContain('2026-01-01');
});

it('exports only consents within the selected date range', function () {
    $path = storage_path('app/testing/complihance-consents-filtered.csv');

    File::delete($path);

    Consent::query()->create([
        'consent_uuid' => (string) Str::uuid(),
        'source' => 'banner',
        'accepted_categories' => ['necessary'],
        'rejected_categories' => [],
        'vendors' => [],
        'policy_version' => '2026-01-01',
        'cookie_configuration_version' => '2026-01-01',
        'anonymous_id' => 'old_consent',
        'accepted_at' => now()->subMonths(2),
    ]);

    Consent::query()->create([
        'consent_uuid' => (string) Str::uuid(),
        'source' => 'banner',
        'accepted_categories' => ['necessary'],
        'rejected_categories' => [],
        'vendors' => [],
        'policy_version' => '2026-01-01',
        'cookie_configuration_version' => '2026-01-01',
        'anonymous_id' => 'current_consent',
        'accepted_at' => now(),
    ]);

    $this->artisan('complihance:export-consents', [
        '--from' => now()->subDay()->toDateString(),
        '--to' => now()->addDay()->toDateString(),
        '--path' => $path,
    ])
        ->assertSuccessful();

    $csv = File::get($path);

    expect($csv)
        ->toContain('current_consent')
        ->not->toContain('old_consent');
});
