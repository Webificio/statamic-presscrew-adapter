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

## Impostazioni nel CP

Da **Addons → PressCrew Adapter** (richiede Statamic 6.30+) si configura tutto senza toccare file:

- **Connessione:** attiva/disattiva l'adapter, mostra URL del sito e rotta da incollare in PressCrew e propone un token già generato (valido dopo aver premuto Salva).
- **Collezioni e campi:** si spuntano le collezioni a cui PressCrew può pubblicare e, per ciascuna, i campi del blueprint che l'AI può compilare. Si propongono solo i campi compatibili: testo, area di testo, markdown, select/radio/gruppo di pulsanti, numero, interruttore.
- **Avanzate:** rotta, limite di richieste, fuso orario.

I valori salvati nel CP (`resources/addons/statamic-adapter.yaml`) prevalgono su `config/presscrew-adapter.php` e sul `.env`; se un valore è vuoto si usa il file di configurazione. Titolo, testo ed estratto sono sempre inviati. Se le rotte sono in cache (`php artisan route:cache`) serve `php artisan route:clear` dopo aver cambiato rotta o attivazione.

## Schema e campi compilati dall'AI

Le stesse scelte si possono fare nel file di configurazione:

```php
'collections' => [
    'blog' => ['blueprint' => 'articolo', 'fields' => ['seo_title', 'meta_description', 'category']],
],
```

- `GET /webhooks/presscrew/schema` (stesso token) restituisce, per ogni collezione, i campi con tipo, etichetta, istruzioni, opzioni delle select e limite di caratteri. PressCrew lo legge quando colleghi il sito.
- In pubblicazione PressCrew invia `collection` (facoltativa, default la prima) e `fields: {handle: valore}`. I valori sono validati con le regole del blueprint, più opzioni delle select e `character_limit`; un campo non abilitato è rifiutato (422).
- Senza `collections` si usa solo `collection`/`blueprint`, come prima.
