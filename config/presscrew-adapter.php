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
