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
    | Lifetime of the consent cookie expressed in minutes.
    | Default: 12 months.
    |
    */
    'cookie_lifetime' => env('COMPLIHANCE_COOKIE_LIFETIME', 60 * 24 * 365),

    /*
    |--------------------------------------------------------------------------
    | Package Routes
    |--------------------------------------------------------------------------
    |
    | Configure web and API routes exposed by Complihance.
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
    | Default Banner Texts
    |--------------------------------------------------------------------------
    |
    | Texts used by the default consent banner views.
    |
    */
    'texts' => [
        'eyebrow' => 'Your cookie preferences.',
        'title' => 'Personalized experiences with full control',

        'description' => [
            'This website uses analytics and tracking cookies, as well as similar technologies. By consenting to the use of cookies and the related processing of personal data, you allow us to analyze the content you view and enable us to display advertisements tailored to your interests.',
            'You can provide consent by clicking "Accept all", selecting your individual preferences, or rejecting cookies by using the dedicated button and closing the banner. You have the right to withdraw your consent at any time.',
        ],

        'cookie_policy_label' => 'Cookie Policy',
        'cookie_policy_url' => env('COMPLIHANCE_COOKIE_POLICY_URL', '/cookie-policy'),

        'actions' => [
            'save' => 'Save preferences',
            'reject' => 'Reject all',
            'accept_all' => 'Accept all',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Consent Categories
    |--------------------------------------------------------------------------
    |
    | Configure the available consent categories exposed by the package.
    |
    */
    'categories' => [
        'necessary' => [
            'label' => 'Strictly necessary cookies',
            'description' => 'Strictly necessary cookies are essential to ensure the proper functioning and security of the website.',
            'required' => true,
        ],

        'analytics' => [
            'label' => 'Analytics and performance cookies',
            'description' => 'These cookies allow the collection of information, including aggregated data, about user behavior during browsing.',
            'required' => false,

            // 'vendors' => [
            //     'google_analytics' => [
            //         'label' => 'Google Analytics',
            //         'description' => 'Traffic measurement and usage statistics.',
            //     ],
            //     'hotjar' => [
            //         'label' => 'Hotjar',
            //         'description' => 'User behavior analysis.',
            //     ],
            // ],
        ],

        'marketing' => [
            'label' => 'Marketing cookies',
            'description' => 'These cookies allow the display of personalized content and advertisements based on user interests and enable measurement of marketing campaign effectiveness.',
            'required' => false,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Policy Versioning
    |--------------------------------------------------------------------------
    |
    | Current version of the privacy and cookie policies.
    |
    */
    'policy_version' => env('COMPLIHANCE_POLICY_VERSION', '1.0.0'),

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
    | Configure versioned policies and their storage drivers.
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
        'cookie_banner' => 'Cookie banner',
        'custom_form' => 'Custom form',
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
        'expired_action' => env('COMPLIHANCE_EXPIRED_CONSENTS_ACTION', 'anonymize'), // Supported values: anonymize, delete
        'chunk_size' => env('COMPLIHANCE_RETENTION_CHUNK_SIZE', 100),
    ],

    /*
    |--------------------------------------------------------------------------
    | Blocked Content
    |--------------------------------------------------------------------------
    |
    | Configure placeholders and inline consent requests for blocked
    | embedded content.
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
    | After Revoke redirect
    |--------------------------------------------------------------------------
    |
    | Choose where to redirect after consent revoke.
    |
    */
    'after_revoke_redirect_url' => '/',

    /*
    |--------------------------------------------------------------------------
    | Vite Configuration
    |--------------------------------------------------------------------------
    |
    | Configure the Vite development server used during package development.
    |
    */
    'vite' => [
        'dev_server' => env('COMPLIHANCE_VITE_DEV_SERVER'),
    ],
];
