<?php

return [
    'tab_connection' => 'Connection',
    'tab_collections' => 'Collections and fields',
    'tab_advanced' => 'Advanced',

    'promo' => 'Don\'t have an account yet? Use the code <strong><code>:code</code></strong> for <strong>10% off</strong> PressCrew, the AI newsroom that writes in your style and publishes to Statamic. :link',
    'promo_link' => 'Discover PressCrew →',

    'how_label' => 'How to connect PressCrew',
    'how' => '<ol><li>In PressCrew, add a <strong>Statamic</strong> destination.</li><li>Site URL: <code>:url</code></li><li>Route: <code>:route</code></li><li>Token: copy the one below <strong>after pressing Save</strong>.</li></ol>',

    'enabled_label' => 'Adapter enabled',
    'enabled_instructions' => 'When off, the site no longer receives articles from PressCrew.',

    'token_label' => 'Access token',
    'token_has' => 'If empty, the token from the .env file is used. To change it, enter a long random value and update PressCrew too.',
    'token_new' => 'Generated for you: press Save to activate it, then paste it into PressCrew.',

    'where_label' => 'Where PressCrew publishes',
    'where_instructions' => 'Pick the collections and, for each one, the fields the PressCrew AI may fill in (title, body and excerpt are always sent). With no choice, the «:collection» collection is used with no extra fields.',
    'collections_label' => 'Collections',
    'blueprint_label' => 'Blueprint of «:collection»',
    'fields_label' => 'Fields filled in by the AI in «:collection»',
    'no_fields' => 'No compatible fields: text, textarea, markdown, choices, number and toggle are supported.',

    'route_label' => 'Route',
    'route_instructions' => 'Address that receives the articles. If routes are cached, run `php artisan route:clear` after changing it.',
    'throttle_label' => 'Rate limit',
    'throttle_instructions' => 'Format «requests,minutes», for example 30,1.',
    'timezone_label' => 'Time zone',
    'timezone_instructions' => 'Used for the date of articles sent without one, for example Europe/Rome.',
];
