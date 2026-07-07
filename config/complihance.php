<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Consent Cookie
    |--------------------------------------------------------------------------
    |
    | Name of the cookie used to persist the user's consent preferences.
    |
    */
    'cookie_name' => env('COMPLIHANCE_COOKIE_NAME', 'complihance_consent'),

    /*
    |--------------------------------------------------------------------------
    | Consent Cookie Lifetime
    |--------------------------------------------------------------------------
    |
    | Lifetime of the consent cookie, expressed in minutes.
    | Default: 12 months.
    |
    */
    'cookie_lifetime' => env('COMPLIHANCE_COOKIE_LIFETIME', 60 * 24 * 365),

    /*
    |--------------------------------------------------------------------------
    | Cookie Policy URL
    |--------------------------------------------------------------------------
    |
    | URL used to link the cookie policy from the consent banner.
    |
    */
    'cookie_policy_url' => env('COMPLIHANCE_COOKIE_POLICY_URL', '/cookie-policy'),

    /*
    |--------------------------------------------------------------------------
    | Package Routes
    |--------------------------------------------------------------------------
    |
    | Configure the web and API routes exposed by Complihance.
    |
    */
    'routes' => [
        'prefix' => env('COMPLIHANCE_ROUTE_PREFIX', 'complihance'),
        'middleware' => ['web'],

        'api_prefix' => env('COMPLIHANCE_API_ROUTE_PREFIX', 'complihance/api'),
        'api_middleware' => ['web'],
    ],

    /*
    |--------------------------------------------------------------------------
    | Consent Banner
    |--------------------------------------------------------------------------
    |
    | Enable or disable the default consent banner.
    |
    */
    'banner' => [
        'enabled' => env('COMPLIHANCE_BANNER_ENABLED', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | Data Sources and Localization
    |--------------------------------------------------------------------------
    |
    | Define where categories, technologies, and localized texts are stored.
    |
    */
    'data' => [
        'categories_path' => env(
            'COMPLIHANCE_CATEGORIES_PATH',
            resource_path('vendor/complihance/categories.json')
        ),

        'technologies_path' => env(
            'COMPLIHANCE_TECHNOLOGIES_PATH',
            resource_path('vendor/complihance/technologies.json')
        ),

        'texts_path' => env(
            'COMPLIHANCE_TEXTS_PATH',
            resource_path('vendor/complihance/texts.json')
        ),

        'fallback_locale' => env('COMPLIHANCE_FALLBACK_LOCALE', 'en'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Cookie Configuration Version
    |--------------------------------------------------------------------------
    |
    | Increment this value whenever the cookie configuration changes and
    | consent renewal should be required.
    |
    */
    'cookie_configuration_version' => env(
        'COMPLIHANCE_COOKIE_CONFIGURATION_VERSION',
        '1.0.0'
    ),

    /*
    |--------------------------------------------------------------------------
    | Anonymous Identifier Cookie
    |--------------------------------------------------------------------------
    |
    | Cookie used to identify anonymous visitors across sessions.
    |
    */
    'anonymous_cookie_name' => env(
        'COMPLIHANCE_ANONYMOUS_COOKIE_NAME',
        'complihance_anonymous_id'
    ),

    /*
    |--------------------------------------------------------------------------
    | Google Consent Mode
    |--------------------------------------------------------------------------
    |
    | Configure Google Consent Mode integration and category mappings.
    |
    */
    'consent_mode' => [
        'enabled' => env('COMPLIHANCE_CONSENT_MODE_ENABLED', true),

        'default' => [
            'ad_storage' => 'denied',
            'analytics_storage' => 'denied',
            'ad_user_data' => 'denied',
            'ad_personalization' => 'denied',
            'functionality_storage' => 'denied',
            'personalization_storage' => 'denied',
            'security_storage' => 'granted',
        ],

        'mapping' => [
            'necessary' => [
                'security_storage',
            ],

            'analytics' => [
                'analytics_storage',
            ],

            'marketing' => [
                'ad_storage',
                'ad_user_data',
                'ad_personalization',
            ],

            'functional' => [
                'functionality_storage',
                'personalization_storage',
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Granular Vendor Consent
    |--------------------------------------------------------------------------
    |
    | Enable vendor-level consent management.
    |
    */
    'granular_consent' => [
        'enabled' => env('COMPLIHANCE_GRANULAR_CONSENT_ENABLED', false),
    ],

    /*
    |--------------------------------------------------------------------------
    | Policy Definitions
    |--------------------------------------------------------------------------
    |
    | Configure the policies managed by Complihance. Each policy has its own
    | version and can require renewed acceptance when that version changes.
    |
    */
    'policies' => [
        'privacy' => [
            'driver' => 'blade',
            'version' => '2026-06-30',
            'title' => 'Privacy Policy',
            'view' => 'complihance::policies.privacy',
        ],

        'cookie' => [
            'driver' => 'blade',
            'version' => '2026-06-30',
            'title' => 'Cookie Policy',
            'view' => 'complihance::policies.cookie',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Policy Acceptance Sources
    |--------------------------------------------------------------------------
    |
    | List of available sources used when recording policy acceptance.
    |
    */
    'policy_acceptance_sources' => [
        'banner',
        'preferences',
        'api',
        'seeder',
        'console',
    ],

    /*
    |--------------------------------------------------------------------------
    | Consent Retention
    |--------------------------------------------------------------------------
    |
    | Configure consent retention and expiration handling.
    |
    */
    'retention' => [
        'enabled' => env('COMPLIHANCE_RETENTION_ENABLED', true),
        'consent_retention_months' => env('COMPLIHANCE_CONSENT_RETENTION_MONTHS', 12),
        'expired_action' => env('COMPLIHANCE_EXPIRED_CONSENTS_ACTION', 'anonymize'), // Supported values: anonymize, delete.
        'chunk_size' => env('COMPLIHANCE_RETENTION_CHUNK_SIZE', 100),
    ],

    /*
    |--------------------------------------------------------------------------
    | Blocked Content
    |--------------------------------------------------------------------------
    |
    | Configure placeholders and inline consent requests for blocked embedded
    | content.
    |
    */
    'blocked_content' => [
        'inline_consent' => env('COMPLIHANCE_BLOCKED_CONTENT_INLINE_CONSENT', true),

        'placeholders' => [
            'default' => [
                'title' => 'Blocked content',
                'description' => 'This content requires :category consent.',
                'button' => 'Accept and view',
            ],

            'youtube' => [
                'title' => 'Blocked YouTube video',
                'description' => 'You must accept marketing cookies to view this video.',
                'button' => 'Accept marketing and watch the video',
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | After Revoke Redirect
    |--------------------------------------------------------------------------
    |
    | URL where users are redirected after consent revocation.
    |
    */
    'after_revoke_redirect_url' => env('COMPLIHANCE_AFTER_REVOKE_REDIRECT_URL', '/'),

    /*
    |--------------------------------------------------------------------------
    | Content Security Policy
    |--------------------------------------------------------------------------
    |
    | Complihance supports CSP nonces for the inline scripts required to expose
    | the frontend configuration and initialize Google Consent Mode.
    |
    | You may provide a static nonce value:
    |
    |     'csp_nonce' => 'your-generated-nonce',
    |
    | or a resolver callback that returns the nonce dynamically:
    |
    |     'csp_nonce_resolver' => fn () => csp_nonce(),
    |
    | If both options are null, Complihance will render inline scripts without
    | a nonce attribute.
    |
    */
    'csp_nonce' => null,

    'csp_nonce_resolver' => null,

    /*
    |--------------------------------------------------------------------------
    | Scanner
    |--------------------------------------------------------------------------
    |
    | Configure the Complihance cookie scanner.
    |
    */
    'scanner' => [
        'sitemap_limit' => env('COMPLIHANCE_SCANNER_SITEMAP_LIMIT', 100),
    ],
];
