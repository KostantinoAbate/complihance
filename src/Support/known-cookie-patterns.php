<?php

return [
    [
        'pattern' => '/^complihance_anonymous_id$/',
        'category' => 'necessary',
        'vendor' => 'Complihance',
        'duration' => '12 months',
        'translations' => [
            'en' => [
                'name' => 'Complihance Anonymous ID',
                'description' => 'Used to distinguish users.',
            ],
            'it' => [
                'name' => 'Complihance Anonymous ID',
                'description' => 'Usato per distinguere gli utenti.',
            ],
        ],
    ],

    [
        'pattern' => '/^laravel_session$/',
        'category' => 'necessary',
        'vendor' => 'Laravel',
        'duration' => 'Session',
        'description' => 'Maintains the user session.',
    ],

    [
        'pattern' => '/^XSRF-TOKEN$/',
        'category' => 'necessary',
        'vendor' => 'Laravel',
        'duration' => 'Session',
        'description' => 'Helps protect the application from cross-site request forgery attacks.',
    ],

    [
        'pattern' => '/^complihance_consent$/',
        'category' => 'necessary',
        'vendor' => 'Complihance',
        'duration' => '12 months',
        'description' => 'Stores the user cookie consent preferences.',
    ],

    [
        'pattern' => '/^_ga($|_)/',
        'category' => 'analytics',
        'vendor' => 'Google Analytics',
        'duration' => '2 years',
        'description' => 'Used to distinguish users.',
    ],

    [
        'pattern' => '/^_gid$/',
        'category' => 'analytics',
        'vendor' => 'Google Analytics',
        'duration' => '24 hours',
        'description' => 'Used to distinguish users.',
    ],

    [
        'pattern' => '/^_gat/',
        'category' => 'analytics',
        'vendor' => 'Google Analytics',
        'duration' => '1 minute',
        'description' => 'Used to throttle request rate.',
    ],

    [
        'pattern' => '/^_gcl_/',
        'category' => 'marketing',
        'vendor' => 'Google Ads',
        'duration' => '90 days',
        'description' => 'Used to store advertising campaign information.',
    ],

    [
        'pattern' => '/^_fbp$/',
        'category' => 'marketing',
        'vendor' => 'Meta',
        'duration' => '3 months',
        'description' => 'Used to deliver and measure advertising.',
    ],

    [
        'pattern' => '/^_fbc$/',
        'category' => 'marketing',
        'vendor' => 'Meta',
        'duration' => '3 months',
        'description' => 'Used to store the last Facebook click identifier.',
    ],

    [
        'pattern' => '/^_hj/',
        'category' => 'analytics',
        'vendor' => 'Hotjar',
        'duration' => 'Variable',
        'description' => 'Used for analytics and user behavior tracking.',
    ],

    [
        'pattern' => '/^_clck$|^_clsk$/',
        'category' => 'analytics',
        'vendor' => 'Microsoft Clarity',
        'duration' => 'Variable',
        'description' => 'Used for website analytics and session recording.',
    ],

    [
        'pattern' => '/^language$|^locale$/',
        'category' => 'functional',
        'vendor' => 'Application',
        'duration' => 'Variable',
        'description' => 'Stores the user language preference.',
    ],

    [
        'pattern' => '/^theme$|^appearance$/',
        'category' => 'functional',
        'vendor' => 'Application',
        'duration' => 'Variable',
        'description' => 'Stores the user interface appearance preference.',
    ],

    [
        'pattern' => '/^timezone$/',
        'category' => 'functional',
        'vendor' => 'Application',
        'duration' => 'Variable',
        'description' => 'Stores the user timezone preference.',
    ],
];
