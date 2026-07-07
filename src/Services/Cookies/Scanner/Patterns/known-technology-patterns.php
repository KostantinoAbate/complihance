<?php

return [
    [
        'type' => 'cookie',
        'pattern' => '/^complihance_anonymous_id$/',
        'category' => 'necessary',
        'vendor' => 'Complihance',
        'translations' => [
            'en' => [
                'name' => 'Anonymous visitor identifier',
                'description' => 'Stores an anonymous identifier used to associate consent records with anonymous visitors.',
                'duration' => '12 months',
            ],
            'it' => [
                'name' => 'Identificativo anonimo visitatore',
                'description' => 'Memorizza un identificativo anonimo usato per associare i consensi ai visitatori anonimi.',
                'duration' => '12 mesi',
            ],
        ],
    ],
    [
        'type' => 'cookie',
        'pattern' => '/^(laravel_session|.*[-_]session)$/',
        'category' => 'necessary',
        'vendor' => 'Laravel',
        'translations' => [
            'en' => [
                'name' => 'Laravel session',
                'description' => 'Maintains the user session.',
                'duration' => 'Session',
            ],
            'it' => [
                'name' => 'Sessione Laravel',
                'description' => 'Mantiene la sessione dell’utente.',
                'duration' => 'Sessione',
            ],
        ],
    ],
    [
        'type' => 'cookie',
        'pattern' => '/^XSRF-TOKEN$/',
        'category' => 'necessary',
        'vendor' => 'Laravel',
        'translations' => [
            'en' => [
                'name' => 'CSRF protection token',
                'description' => 'Helps protect the application from cross-site request forgery attacks.',
                'duration' => 'Session',
            ],
            'it' => [
                'name' => 'Token di protezione CSRF',
                'description' => 'Aiuta a proteggere l’applicazione da attacchi cross-site request forgery.',
                'duration' => 'Sessione',
            ],
        ],
    ],
    [
        'type' => 'cookie',
        'pattern' => '/^complihance_consent$/',
        'category' => 'necessary',
        'vendor' => 'Complihance',
        'translations' => [
            'en' => [
                'name' => 'Consent cookie',
                'description' => 'Stores the user cookie consent preferences.',
                'duration' => '12 months',
            ],
            'it' => [
                'name' => 'Cookie di consenso',
                'description' => 'Memorizza le preferenze di consenso cookie dell’utente.',
                'duration' => '12 mesi',
            ],
        ],
    ],
    [
        'type' => 'cookie',
        'pattern' => '/^_ga($|_)/',
        'category' => 'analytics',
        'vendor' => 'Google Analytics',
        'translations' => [
            'en' => [
                'name' => '_ga',
                'description' => 'Used to distinguish users.',
                'duration' => '2 years',
            ],
            'it' => [
                'name' => '_ga',
                'description' => 'Utilizzato per distinguere gli utenti.',
                'duration' => '2 anni',
            ],
        ],
    ],
    [
        'type' => 'cookie',
        'pattern' => '/^_gid$/',
        'category' => 'analytics',
        'vendor' => 'Google Analytics',
        'translations' => [
            'en' => [
                'name' => '_gid',
                'description' => 'Used to distinguish users.',
                'duration' => '24 hours',
            ],
            'it' => [
                'name' => '_gid',
                'description' => 'Utilizzato per distinguere gli utenti.',
                'duration' => '24 ore',
            ],
        ],
    ],
    [
        'type' => 'cookie',
        'pattern' => '/^_gat/',
        'category' => 'analytics',
        'vendor' => 'Google Analytics',
        'translations' => [
            'en' => [
                'name' => '_gat',
                'description' => 'Used to throttle request rate.',
                'duration' => '1 minute',
            ],
            'it' => [
                'name' => '_gat',
                'description' => 'Utilizzato per limitare la frequenza delle richieste.',
                'duration' => '1 minuto',
            ],
        ],
    ],
    [
        'type' => 'cookie',
        'pattern' => '/^_gcl_/',
        'category' => 'marketing',
        'vendor' => 'Google Ads',
        'translations' => [
            'en' => [
                'name' => '_gcl_*',
                'description' => 'Used to store advertising campaign information.',
                'duration' => '90 days',
            ],
            'it' => [
                'name' => '_gcl_*',
                'description' => 'Utilizzato per memorizzare informazioni sulle campagne pubblicitarie.',
                'duration' => '90 giorni',
            ],
        ],
    ],
    [
        'type' => 'cookie',
        'pattern' => '/^_fbp$/',
        'category' => 'marketing',
        'vendor' => 'Meta',
        'translations' => [
            'en' => [
                'name' => '_fbp',
                'description' => 'Used to deliver and measure advertising.',
                'duration' => '3 months',
            ],
            'it' => [
                'name' => '_fbp',
                'description' => 'Utilizzato per mostrare e misurare contenuti pubblicitari.',
                'duration' => '3 mesi',
            ],
        ],
    ],
    [
        'type' => 'cookie',
        'pattern' => '/^_fbc$/',
        'category' => 'marketing',
        'vendor' => 'Meta',
        'translations' => [
            'en' => [
                'name' => '_fbc',
                'description' => 'Used to store the last Facebook click identifier.',
                'duration' => '3 months',
            ],
            'it' => [
                'name' => '_fbc',
                'description' => 'Utilizzato per memorizzare l’identificativo dell’ultimo clic Facebook.',
                'duration' => '3 mesi',
            ],
        ],
    ],

    [
        'type' => 'script',
        'pattern' => '/googletagmanager\.com\/gtm\.js/i',
        'category' => 'analytics',
        'vendor' => 'Google Tag Manager',
        'translations' => [
            'en' => [
                'name' => 'Google Tag Manager',
                'description' => 'Loads the Google Tag Manager container script.',
                'duration' => 'N/A',
            ],
            'it' => [
                'name' => 'Google Tag Manager',
                'description' => 'Carica lo script container di Google Tag Manager.',
                'duration' => 'N/A',
            ],
        ],
    ],
    [
        'type' => 'script',
        'pattern' => '/googletagmanager\.com\/gtag\/js/i',
        'category' => 'analytics',
        'vendor' => 'Google Analytics',
        'translations' => [
            'en' => [
                'name' => 'Google Analytics gtag.js',
                'description' => 'Loads the Google Analytics tracking library.',
                'duration' => 'N/A',
            ],
            'it' => [
                'name' => 'Google Analytics gtag.js',
                'description' => 'Carica la libreria di tracciamento Google Analytics.',
                'duration' => 'N/A',
            ],
        ],
    ],
    [
        'type' => 'script',
        'pattern' => '/google-analytics\.com\/analytics\.js/i',
        'category' => 'analytics',
        'vendor' => 'Google Analytics',
        'translations' => [
            'en' => [
                'name' => 'Google Analytics analytics.js',
                'description' => 'Loads the legacy Google Analytics tracking library.',
                'duration' => 'N/A',
            ],
            'it' => [
                'name' => 'Google Analytics analytics.js',
                'description' => 'Carica la libreria legacy di tracciamento Google Analytics.',
                'duration' => 'N/A',
            ],
        ],
    ],
    [
        'type' => 'script',
        'pattern' => '/connect\.facebook\.net\/.*\/fbevents\.js/i',
        'category' => 'marketing',
        'vendor' => 'Meta Pixel',
        'translations' => [
            'en' => [
                'name' => 'Meta Pixel',
                'description' => 'Loads the Meta Pixel tracking library.',
                'duration' => 'N/A',
            ],
            'it' => [
                'name' => 'Meta Pixel',
                'description' => 'Carica la libreria di tracciamento Meta Pixel.',
                'duration' => 'N/A',
            ],
        ],
    ],
    [
        'type' => 'script',
        'pattern' => '/static\.hotjar\.com|script\.hotjar\.com/i',
        'category' => 'analytics',
        'vendor' => 'Hotjar',
        'translations' => [
            'en' => [
                'name' => 'Hotjar',
                'description' => 'Loads the Hotjar analytics and behavior tracking script.',
                'duration' => 'N/A',
            ],
            'it' => [
                'name' => 'Hotjar',
                'description' => 'Carica lo script di analisi e tracciamento comportamentale Hotjar.',
                'duration' => 'N/A',
            ],
        ],
    ],
    [
        'type' => 'script',
        'pattern' => '/youtube\.com\/iframe_api|youtube\.com\/s\/player/i',
        'category' => 'marketing',
        'vendor' => 'YouTube',
        'translations' => [
            'en' => [
                'name' => 'YouTube player script',
                'description' => 'Loads scripts required to display or control embedded YouTube videos.',
                'duration' => 'N/A',
            ],
            'it' => [
                'name' => 'Script player YouTube',
                'description' => 'Carica gli script necessari per visualizzare o controllare video YouTube incorporati.',
                'duration' => 'N/A',
            ],
        ],
    ],
    [
        'type' => 'script',
        'pattern' => '/player\.vimeo\.com\/api\/player\.js/i',
        'category' => 'marketing',
        'vendor' => 'Vimeo',
        'translations' => [
            'en' => [
                'name' => 'Vimeo player script',
                'description' => 'Loads scripts required to display or control embedded Vimeo videos.',
                'duration' => 'N/A',
            ],
            'it' => [
                'name' => 'Script player Vimeo',
                'description' => 'Carica gli script necessari per visualizzare o controllare video Vimeo incorporati.',
                'duration' => 'N/A',
            ],
        ],
    ],
    [
        'type' => 'script',
        'pattern' => '/snap\.licdn\.com\/li\.lms-analytics/i',
        'category' => 'marketing',
        'vendor' => 'LinkedIn Insight Tag',
        'translations' => [
            'en' => [
                'name' => 'LinkedIn Insight Tag',
                'description' => 'Loads the LinkedIn advertising and conversion tracking script.',
                'duration' => 'N/A',
            ],
            'it' => [
                'name' => 'LinkedIn Insight Tag',
                'description' => 'Carica lo script LinkedIn per advertising e tracciamento conversioni.',
                'duration' => 'N/A',
            ],
        ],
    ],
    [
        'type' => 'script',
        'pattern' => '/analytics\.tiktok\.com\/i18n\/pixel/i',
        'category' => 'marketing',
        'vendor' => 'TikTok Pixel',
        'translations' => [
            'en' => [
                'name' => 'TikTok Pixel',
                'description' => 'Loads the TikTok advertising and conversion tracking script.',
                'duration' => 'N/A',
            ],
            'it' => [
                'name' => 'TikTok Pixel',
                'description' => 'Carica lo script TikTok per advertising e tracciamento conversioni.',
                'duration' => 'N/A',
            ],
        ],
    ],
];
