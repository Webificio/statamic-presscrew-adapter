# PressCrew Adapter

Addon Statamic che riceve articoli dalla piattaforma PressCrew (`POST /webhooks/presscrew`) e li salva come bozza o pubblicati.

## Installazione

```bash
composer config repositories.presscrew vcs git@github.com:ORGANIZZAZIONE/statamic-presscrew-adapter.git
composer require presscrew/statamic-adapter
php artisan vendor:publish --tag=presscrew-adapter-config   # opzionale
```

`.env`: `PRESSCREW_ADAPTER_TOKEN=<token lungo e casuale>`

## Richiesta

`Authorization: Bearer <token>`, corpo JSON con `title`, `content` (Markdown), `status` (`draft`|`published`) e, facoltativi, `slug`, `date`, più i campi elencati in `fields` nella configurazione. Uno `slug` già esistente aggiorna l'articolo (risposta 200, altrimenti 201).

## Campi compilati dall'AI (schema)

Nel file di configurazione elenca le collezioni a cui PressCrew può pubblicare e i campi del blueprint che l'AI può compilare:

```php
'collections' => [
    'blog' => ['blueprint' => 'articolo', 'fields' => ['seo_title', 'meta_description', 'category']],
],
```

- `GET /webhooks/presscrew/schema` (stesso token) restituisce, per ogni collezione, i campi con tipo, etichetta, istruzioni, opzioni delle select e limite di caratteri. PressCrew lo legge quando colleghi il sito.
- Tipi supportati: `text`, `textarea`, `markdown`, `select`, `radio`, `button_group`, `integer`, `toggle`. Gli altri finiscono in `unsupported`.
- In pubblicazione PressCrew invia `collection` (facoltativa, default la prima) e `fields: {handle: valore}`. I valori sono validati con le regole del blueprint, più opzioni delle select e `character_limit`; un campo non elencato in `fields` è rifiutato (422).
- Senza `collections` si usa solo `collection`/`blueprint`, come prima.
