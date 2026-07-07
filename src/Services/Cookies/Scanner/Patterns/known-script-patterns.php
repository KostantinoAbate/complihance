<?php

return [
    [
        'pattern' => '/googletagmanager\.com\/gtm\.js/i',
        'category' => 'analytics',
        'vendor' => 'Google Tag Manager',
    ],
    [
        'pattern' => '/googletagmanager\.com\/gtag\/js/i',
        'category' => 'analytics',
        'vendor' => 'Google Analytics',
    ],
    [
        'pattern' => '/google-analytics\.com\/analytics\.js/i',
        'category' => 'analytics',
        'vendor' => 'Google Analytics',
    ],
    [
        'pattern' => '/connect\.facebook\.net\/.*\/fbevents\.js/i',
        'category' => 'marketing',
        'vendor' => 'Meta Pixel',
    ],
    [
        'pattern' => '/static\.hotjar\.com|script\.hotjar\.com/i',
        'category' => 'analytics',
        'vendor' => 'Hotjar',
    ],
    [
        'pattern' => '/youtube\.com\/iframe_api|youtube\.com\/s\/player/i',
        'category' => 'marketing',
        'vendor' => 'YouTube',
    ],
    [
        'pattern' => '/player\.vimeo\.com\/api\/player\.js/i',
        'category' => 'marketing',
        'vendor' => 'Vimeo',
    ],
    [
        'pattern' => '/snap\.licdn\.com\/li\.lms-analytics/i',
        'category' => 'marketing',
        'vendor' => 'LinkedIn Insight Tag',
    ],
    [
        'pattern' => '/analytics\.tiktok\.com\/i18n\/pixel/i',
        'category' => 'marketing',
        'vendor' => 'TikTok Pixel',
    ],
];
