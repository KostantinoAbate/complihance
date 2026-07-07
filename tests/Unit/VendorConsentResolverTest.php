<?php

use Illuminate\Support\Facades\Config;
use KostantinoAbate\Complihance\Services\Consent\Resolver\VendorConsentResolver;

beforeEach(function () {
    Config::set('complihance.granular_consent.enabled', true);
    Config::set('complihance.data.technologies_path', __DIR__.'/../Fixtures/granular-cookies.json');
});

it('accepts selected vendor when its category is accepted', function () {
    $vendors = app(VendorConsentResolver::class)->resolve(
        categories: ['analytics'],
        vendors: ['google_analytics'],
    );

    expect($vendors)->toBe(['google_analytics']);
});

it('rejects vendor when its category is accepted but vendor is not selected', function () {
    $vendors = app(VendorConsentResolver::class)->resolve(
        categories: ['analytics'],
        vendors: [],
    );

    expect($vendors)->toBe([]);
});

it('rejects selected vendor when its category is rejected', function () {
    $vendors = app(VendorConsentResolver::class)->resolve(
        categories: ['analytics'],
        vendors: ['meta_pixel'],
    );

    expect($vendors)->toBe([]);
});
