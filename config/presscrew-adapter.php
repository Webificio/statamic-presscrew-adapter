<?php

return [

    // When false, the receiving route is not registered.
    'enabled' => env('PRESSCREW_ADAPTER_ENABLED', true),

    // Token expected in the "Authorization: Bearer <token>" header. Without a token every request is rejected.
    'token' => env('PRESSCREW_ADAPTER_TOKEN'),

    // URL of the (POST) route and rate limit as "max,minutes".
    'route' => env('PRESSCREW_ADAPTER_ROUTE', 'webhooks/presscrew'),
    'throttle' => env('PRESSCREW_ADAPTER_THROTTLE', '30,1'),

    // Collection and blueprint where articles are created.
    'collection' => env('PRESSCREW_ADAPTER_COLLECTION', 'blog'),
    'blueprint' => env('PRESSCREW_ADAPTER_BLUEPRINT', 'articolo'),

    // Collections PressCrew may publish to, with the blueprint fields the AI may fill in.
    // Empty = only the collection above, with no AI-filled fields. The schema is served by GET <route>/schema.
    // Supported types: text, textarea, markdown, select, radio, button_group, integer, toggle.
    // 'collections' => [
    //     'blog' => ['blueprint' => 'article', 'fields' => ['seo_title', 'meta_description', 'category']],
    // ],
    'collections' => [],

    // Time zone for the default date of articles.
    'timezone' => env('PRESSCREW_ADAPTER_TIMEZONE', config('app.timezone')),

    // Media library: asset container and folder where PressCrew uploads images (the container must exist).
    'asset_container' => env('PRESSCREW_ADAPTER_ASSET_CONTAINER', 'assets'),
    'asset_folder' => env('PRESSCREW_ADAPTER_ASSET_FOLDER', 'presscrew'),

    // Allowed categories. Empty array = the "category" field is not validated.
    'categories' => [],

    // Optional fields sent by the platform => validation rules.
    // They are saved with the same handle in the collection's blueprint.
    'fields' => [
        'excerpt' => ['nullable', 'string', 'max:500'],
        'category' => ['nullable', 'string', 'max:100'],
        'read_time' => ['nullable', 'integer', 'min:1', 'max:120'],
        'seo_description' => ['nullable', 'string', 'max:320'],
    ],

];
