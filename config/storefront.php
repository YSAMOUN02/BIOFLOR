<?php

/*
 * Fallbacks only. Live values are edited in /admin/settings (settings table)
 * and read with setting('key'); these apply before the table is seeded.
 */
return [
    'brand'          => 'Bioflor',
    'default_locale' => 'en',

    // URL prefixes the site answers to (rows in the locales table give labels and order).
    'locales'        => ['en', 'km', 'fr'],

    // Languages search engines should not index yet: their pages still show
    // English text, which would compete with the English pages. Remove a
    // prefix once its content is translated.
    'noindex_locales' => ['fr'],

    // Newsletter pop-in: shown after this many page views, then snoozed.
    'newsletter_popup' => [
        'after_pages'      => 2,
        'snooze_days'      => 7,
        'subscribed_days'  => 365,
    ],

    // Brand palette (kept in sync with public/css/site.css custom properties).
    'theme' => [
        'primary'   => '#001660',
        'secondary' => '#4ec2eb',
        'pink'      => '#f7beb6',
        'peach'     => '#fde4d3',
        'cream'     => '#fff3d6',
        'sky'       => '#dbf0fd',
    ],
];
