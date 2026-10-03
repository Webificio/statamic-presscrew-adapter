<?php

namespace PressCrew\Adapter\Support;

use Illuminate\Http\Request;
use Statamic\Facades\Collection;
use Statamic\Fields\Field;

class Targets
{
    /** Tipi di campo che l'AI può compilare, e handle già gestiti direttamente dall'adapter. */
    public const TYPES = ['text', 'textarea', 'markdown', 'select', 'radio', 'button_group', 'integer', 'toggle'];

    public const RESERVED = ['id', 'title', 'slug', 'date', 'status', 'content', 'excerpt'];

    public static function authorize(Request $request): void
    {
        $token = (string) Options::get('token');
        abort_if($token === '' || ! hash_equals($token, (string) $request->bearerToken()), 401, 'Token non valido.');
    }

    /** Collezioni a cui PressCrew può pubblicare: handle => ['blueprint' => ..., 'fields' => [handle, ...]]. */
    public static function all(): array
    {
        $config = config('presscrew-adapter');
        $saved = Options::saved();

        // Pagina impostazioni del CP: collezioni scelte (ignorando quelle eliminate), blueprint e campi per ciascuna.
        $fromCp = collect($saved['collections'] ?? [])
            ->filter(fn ($handle) => Collection::findByHandle($handle))
            ->mapWithKeys(fn ($handle) => [$handle => [
                'blueprint' => $saved[Options::key('blueprint', $handle)] ?? Collection::findByHandle($handle)->entryBlueprints()->first()->handle(),
                'fields' => $saved[Options::key('fields', $handle)] ?? [],
            ]])
            ->all();

        if ($fromCp !== []) {
            return $fromCp;
        }

        return $config['collections'] ?: [$config['collection'] => ['blueprint' => $config['blueprint'], 'fields' => []]];
    }

    /** Opzioni [['value', 'label']] dei campi a scelta, null per gli altri tipi. */
    public static function options(Field $field): ?array
    {
        if (! in_array($field->type(), ['select', 'radio', 'button_group'], true)) {
            return null;
        }

        $result = [];

        // Statamic salva le opzioni come [valore => etichetta], come elenco semplice o come elenco di ['key', 'value'].
        foreach ((array) $field->get('options', []) as $key => $option) {
            if (is_array($option) && isset($option['key'])) {
                $result[] = ['value' => (string) $option['key'], 'label' => (string) ($option['value'] ?? $option['key'])];
            } elseif (is_int($key)) {
                $result[] = ['value' => (string) $option, 'label' => (string) $option];
            } else {
                $result[] = ['value' => (string) $key, 'label' => (string) $option];
            }
        }

        return $result;
    }
}
