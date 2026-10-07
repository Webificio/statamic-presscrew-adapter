# Changelog

## 1.1.1

- Fix: entry date is passed as a Carbon instance instead of a `Y-m-d H:i` string, which Statamic 6.30 rejects (every publish returned 500).

## 1.1.0

- Media library: `GET` and `POST /webhooks/presscrew/assets` to browse and upload images (used by PressCrew's graphics desk).
- Settings: asset container and upload folder (Addons → PressCrew → Advanced, or `PRESSCREW_ADAPTER_ASSET_CONTAINER` / `PRESSCREW_ADAPTER_ASSET_FOLDER`).

## 1.0.2 and earlier

See the git history.
