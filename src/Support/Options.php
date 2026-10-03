<?php

namespace PressCrew\Adapter\Support;

use Statamic\Facades\Addon;
use Statamic\Facades\YAML;

/** Adapter options: what is saved in the Control Panel settings page takes precedence over the config file. */
class Options
{
    /**
     * Values saved by the settings page. The YAML file is read directly instead of Addon::settings(), because
     * the latter builds the blueprint, which in turn reads the saved values (infinite recursion).
     * Limitation: only Statamic's file repository is supported (resources/addons/<slug>.yaml).
     */
    public static function saved(): array
    {
        $addon = Addon::get('presscrew/statamic-adapter');
        $path = $addon ? resource_path("addons/{$addon->slug()}.yaml") : null;

        return $path && is_file($path) ? (YAML::file($path)->parse() ?? []) : [];
    }

    public static function get(string $key): mixed
    {
        $saved = self::saved()[$key] ?? null;

        return $saved !== null && $saved !== '' ? $saved : config("presscrew-adapter.$key");
    }

    /** Form field handle for a collection (field handles do not allow dashes). */
    public static function key(string $prefix, string $collection): string
    {
        return $prefix.'_'.str_replace('-', '_', $collection);
    }
}
