<?php

return [
    [
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
];
