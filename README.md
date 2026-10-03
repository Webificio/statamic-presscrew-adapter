# PressCrew for Statamic

Statamic addon that receives articles from the [PressCrew](https://presscrew.it) AI newsroom platform (`POST /webhooks/presscrew`) and saves them as drafts or published entries.

PressCrew reads your sources, writes articles in your publication's voice and publishes them to your site. New to PressCrew? Use the code **PCSTATAMIC10** for 10% off: [presscrew.it](https://presscrew.it).

## Installation

```bash
composer config repositories.presscrew vcs https://github.com/Webificio/statamic-presscrew-adapter.git
composer require presscrew/statamic-adapter
php artisan vendor:publish --tag=presscrew-adapter-config   # optional
```

Requires PHP 8.3+ and Statamic 6.30+.

Then open **Addons → PressCrew** in the Control Panel to configure everything, or set it in `.env`: `PRESSCREW_ADAPTER_TOKEN=<long random token>`.

## Settings in the Control Panel

Everything can be configured from **Addons → PressCrew**, without touching any file:

- **Connection:** turn the adapter on or off, see the site URL and route to paste into PressCrew, and get a ready-made token (it becomes valid once you press Save).
- **Collections and fields:** tick the collections PressCrew may publish to and, for each one, the blueprint fields the AI may fill in. Only compatible fields are offered: text, textarea, markdown, select/radio/button group, integer and toggle.
- **Advanced:** route, rate limit, time zone.

Values saved in the Control Panel (`resources/addons/statamic-adapter.yaml`) take precedence over `config/presscrew-adapter.php` and `.env`; an empty value falls back to the config file. Title, body and excerpt are always sent. If your routes are cached (`php artisan route:cache`), run `php artisan route:clear` after changing the route or turning the adapter on or off.

## Request

`Authorization: Bearer <token>`, JSON body with `title`, `content` (Markdown), `status` (`draft`|`published`) and, optionally, `slug` and `date`, plus the fields listed under `fields` in the configuration. An existing `slug` updates that entry (response 200, otherwise 201).

## Schema and AI-filled fields

The same choices can be made in the config file:

```php
'collections' => [
    'blog' => ['blueprint' => 'article', 'fields' => ['seo_title', 'meta_description', 'category']],
],
```

- `GET /webhooks/presscrew/schema` (same token) returns, for each collection, its fields with type, label, instructions, select options and character limit. PressCrew reads it when you connect your site.
- When publishing, PressCrew sends `collection` (optional, defaults to the first one) and `fields: {handle: value}`. Values are validated with the blueprint rules plus select options and `character_limit`; a field that is not enabled is rejected (422).
- Without `collections`, only `collection`/`blueprint` are used, as before.
