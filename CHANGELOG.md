# Changelog

## 1.2.0

- Taxonomy fields: a `terms` field (e.g. categories) can be enabled for PressCrew. The schema lists its terms as options (with `max_items`), and publishing accepts a list of existing terms. PressCrew uses it for its per-site category rules.

## 1.1.2

- Media library: thumbnails and resized copies (`name-660x630.jpg` next to `name.jpg`) are no longer listed, only the original.

## 1.1.1

- Fix: entry date is passed as a Carbon instance instead of a `Y-m-d H:i` string, which Statamic 6.30 rejects (every publish returned 500).

## 1.1.0

- Media library: `GET` and `POST /webhooks/presscrew/assets` to browse and upload images (used by PressCrew's graphics desk).
- Settings: asset container and upload folder (Addons → PressCrew → Advanced, or `PRESSCREW_ADAPTER_ASSET_CONTAINER` / `PRESSCREW_ADAPTER_ASSET_FOLDER`).

## 1.0.2 and earlier

See the git history.
