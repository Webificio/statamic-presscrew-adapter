<?php

return [

    // Se false, la rotta di ricezione non viene registrata.
    'enabled' => env('PRESSCREW_ADAPTER_ENABLED', true),

    // Token atteso nell'header "Authorization: Bearer <token>". Senza token tutte le richieste sono rifiutate.
    'token' => env('PRESSCREW_ADAPTER_TOKEN'),

    // URL della rotta (POST) e limite di richieste "max,minuti".
    'route' => env('PRESSCREW_ADAPTER_ROUTE', 'webhooks/presscrew'),
    'throttle' => env('PRESSCREW_ADAPTER_THROTTLE', '30,1'),

    // Collezione e blueprint in cui creare gli articoli.
    'collection' => env('PRESSCREW_ADAPTER_COLLECTION', 'blog'),
    'blueprint' => env('PRESSCREW_ADAPTER_BLUEPRINT', 'articolo'),

    // Collezioni a cui PressCrew può pubblicare, con i campi del blueprint che può compilare l'AI.
    // Vuoto = solo la collezione qui sopra, senza campi compilati dall'AI. Lo schema è letto da GET <route>/schema.
    // Tipi supportati: text, textarea, markdown, select, radio, button_group, integer, toggle.
    // 'collections' => [
    //     'blog' => ['blueprint' => 'articolo', 'fields' => ['seo_title', 'meta_description', 'category']],
    // ],
    'collections' => [],

    // Fuso orario per la data di default degli articoli.
    'timezone' => env('PRESSCREW_ADAPTER_TIMEZONE', config('app.timezone')),

    // Categorie ammesse. Array vuoto = il campo "category" non viene validato.
    'categories' => [],

    // Campi opzionali inviati dalla piattaforma => regole di validazione.
    // Vengono salvati con lo stesso handle nel blueprint della collezione.
    'fields' => [
        'excerpt' => ['nullable', 'string', 'max:500'],
        'category' => ['nullable', 'string', 'max:100'],
        'read_time' => ['nullable', 'integer', 'min:1', 'max:120'],
        'seo_description' => ['nullable', 'string', 'max:320'],
    ],

];
