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
