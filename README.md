# Complihance

[![Latest Version on Packagist](https://img.shields.io/packagist/v/kostantinoabate/complihance.svg?style=flat-square)](https://packagist.org/packages/kostantinoabate/complihance)
[![Total Downloads](https://img.shields.io/packagist/dt/kostantinoabate/complihance.svg?style=flat-square)](https://packagist.org/packages/kostantinoabate/complihance)
[![GitHub Tests Action Status](https://github.com/KostantinoAbate/complihance/actions/workflows/run-tests.yml/badge.svg?branch=main)](https://github.com/KostantinoAbate/complihance/actions/workflows/run-tests.yml)
[![GitHub Code Style Action Status](https://github.com/KostantinoAbate/complihance/actions/workflows/fix-php-code-style-issues.yml/badge.svg?branch=main)](https://github.com/KostantinoAbate/complihance/actions/workflows/fix-php-code-style-issues.yml)
[![License](https://img.shields.io/packagist/l/kostantinoabate/complihance.svg?style=flat-square)](https://github.com/KostantinoAbate/complihance/blob/main/LICENSE.md)

A Laravel package for cookie consent, policy acceptance, consent persistence, Google Consent Mode, cookie scanning and blocked embedded content.

---

### FULL DOCUMENTATION WILL BE AVAILABLE SOON!

---

> ✅ Ready-to-use cookie banner  
> ✅ Consent preferences page  
> ✅ Granular vendor consent  
> ✅ Google Consent Mode support  
> ✅ Consent audit log and retention  
> ✅ Cookie, storage and script scanner

## Installation

Install the package via Composer:

```bash
composer require kostantinoabate/complihance
```

Run the install command:

```bash
php artisan complihance:install
```

Run the migrations:

```bash
php artisan migrate
```

Publish editable data files:

```bash
php artisan vendor:publish --tag=complihance-data
```

## Setup

Add the Complihance scripts to your main layout, preferably inside the `<head>`:

```blade
@complihanceConsentMode
@complihanceScript
```

Render the default banner before the closing `</body>` tag:

```blade
@complihanceBanner
```

Example:

```blade
<!doctype html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta name="csrf-token" content="{{ csrf_token() }}">

    @complihanceConsentMode
    @complihanceScript
</head>
<body>
    {{-- Your application --}}

    @complihanceBanner
</body>
</html>
```

## Preferences page

To let users update or revoke their consent, render the preferences component in any Blade view:

```blade
@complihancePreferences
```

## Cookie policy table

Render a cookie/technology table:

```blade
@complihanceCookieTable
```

Render only one category:

```blade
@complihanceCookieTable('analytics')
```

## Configuration

The config file is published to:

```txt
config/complihance.php
```

Main options:

```php
'cookie_name' => 'complihance_consent',

'cookie_lifetime' => 60 * 24 * 365,

'cookie_policy_url' => '/cookie-policy',

'banner' => [
    'enabled' => true,
],

'cookie_configuration_version' => '1.0.0',

'granular_consent' => [
    'enabled' => false,
],

'consent_mode' => [
    'enabled' => true,
],
```

When your cookie configuration changes, increment:

```php
'cookie_configuration_version' => '1.0.1',
```

Users will be asked to renew their consent.

## Data files

Editable data files can be published with:

```bash
php artisan vendor:publish --tag=complihance-data
```

Published files:

```txt
resources/vendor/complihance/categories.json
resources/vendor/complihance/technologies.json
resources/vendor/complihance/texts.json
```

## Consent categories

Default categories are:

- `necessary`
- `analytics`
- `marketing`
- `functional`

Example category:

```json
{
    "analytics": {
        "required": false,
        "enabled": true,
        "consent_mode": [
            "analytics_storage"
        ],
        "translations": {
            "en": {
                "label": "Analytics and performance cookies",
                "description": "These cookies allow the collection of information about user behavior."
            }
        }
    }
}
```

## Technologies and vendors

Technologies are configured in `technologies.json`.

Example:

```json
{
    "_ga": {
        "category": "analytics",
        "vendor": "Google Analytics",
        "pattern": "^_ga($|_)",
        "translations": {
            "en": {
                "name": "_ga",
                "description": "Used to distinguish users.",
                "duration": "2 years"
            }
        }
    }
}
```

Enable vendor-level consent:

```php
'granular_consent' => [
    'enabled' => true,
],
```

## Google Consent Mode

Google Consent Mode is enabled by default.

```php
'consent_mode' => [
    'enabled' => true,
],
```

Complihance renders the default consent state through:

```blade
@complihanceConsentMode
```

Consent Mode values are updated automatically when the user saves or changes preferences.

## Blocking embedded content

Complihance can prevent embedded content from loading before consent is granted.

Example:

```blade
<iframe
    {!! app(\KostantinoAbate\Complihance\Services\Cookies\BlockedContent\BlockedContentAttributes::class)
        ->render(category: 'marketing', src: 'https://www.youtube.com/embed/example') !!}
    width="560"
    height="315"
></iframe>
```

Or with the Blade directive:

```blade
<iframe
    @complihanceBlockedContent('marketing', 'https://www.youtube.com/embed/example', 'youtube')
    width="560"
    height="315"
></iframe>
```

## Frontend API

Complihance exposes a frontend API on `window.Complihance`.

```js
window.Complihance.hasConsent();
window.Complihance.requiresRenewal();

window.Complihance.canUse('analytics');
window.Complihance.canUseVendor('google_analytics');

window.Complihance.acceptAll();
window.Complihance.rejectAll();
window.Complihance.revoke();

window.Complihance.onConsentChanged((consent) => {
    // Initialize services after consent changes.
});
```

## HTTP API

Default API prefix:

```txt
/complihance/api
```

Available endpoints:

```txt
GET    /complihance/api/consent
POST   /complihance/api/consent
PATCH  /complihance/api/consent
DELETE /complihance/api/consent
GET    /complihance/api/consent/status
GET    /complihance/api/configuration
```

Example payload:

```json
{
    "source": "api",
    "categories": ["necessary", "analytics"],
    "vendors": ["google_analytics"]
}
```

## Artisan commands

Export collected consents:

```bash
php artisan complihance:export-consents
```

Export consents for a date range:

```bash
php artisan complihance:export-consents --from=2026-01-01 --to=2026-12-31
```

Apply retention rules:

```bash
php artisan complihance:retention
```

Preview retention without changing data:

```bash
php artisan complihance:retention --dry-run
```

Scan cookies, storage and scripts:

```bash
php artisan complihance:scan-cookies https://example.com
```

Scan only HTTP `Set-Cookie` headers:

```bash
php artisan complihance:scan-cookies https://example.com --http-header-only
```

Scan a sitemap:

```bash
php artisan complihance:scan-cookies https://example.com/sitemap.xml --sitemap
```

Export a scan report:

```bash
php artisan complihance:scan-cookies https://example.com --report=json
```

Compare two scans:

```bash
php artisan complihance:scan-diff <from-scan-id-or-uuid> <to-scan-id-or-uuid>
```

Reset local Complihance data:

```bash
php artisan complihance:reset --force
```

## Cookie scanner requirements

Browser-based scanning requires Playwright and Chromium:

```bash
npm install -D playwright
npx playwright install chromium
```

In Docker/Linux environments, Chromium system dependencies may also be required.

Alternatively, use the HTTP-only scanner:

```bash
php artisan complihance:scan-cookies https://example.com --http-header-only
```

## Testing

Run the test suite:

```bash
composer test
```

## License

The MIT License (MIT). See [LICENSE.md](LICENSE.md) for more information.
