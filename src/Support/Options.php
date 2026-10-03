<?php

namespace PressCrew\Adapter\Support;

use Statamic\Facades\Addon;
use Statamic\Facades\YAML;

/** Opzioni dell'adapter: ciò che è salvato nella pagina impostazioni del CP ha la precedenza sul file di configurazione. */
class Options
{
    /**
     * Valori salvati dalla pagina impostazioni. Si legge il file YAML e non Addon::settings(), perché
     * quest'ultimo costruisce il blueprint, che a sua volta legge i valori salvati (ricorsione infinita).
     * ponytail: solo il repository a file di Statamic (resources/addons/<slug>.yaml).
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

    /** Handle di un campo del form per una collezione (gli handle dei campi non ammettono il trattino). */
    public static function key(string $prefix, string $collection): string
    {
        return $prefix.'_'.str_replace('-', '_', $collection);
    }
}
