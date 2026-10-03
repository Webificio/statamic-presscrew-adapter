<?php

namespace PressCrew\Adapter\Support;

use Illuminate\Http\Request;
use Statamic\Fields\Field;

class Targets
{
    public static function authorize(Request $request): void
    {
        $token = (string) config('presscrew-adapter.token');
        abort_if($token === '' || ! hash_equals($token, (string) $request->bearerToken()), 401, 'Token non valido.');
    }

    /** Collezioni a cui PressCrew può pubblicare: handle => ['blueprint' => ..., 'fields' => [handle, ...]]. */
    public static function all(): array
    {
        $config = config('presscrew-adapter');

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
