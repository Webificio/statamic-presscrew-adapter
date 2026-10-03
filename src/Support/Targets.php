<?php

namespace PressCrew\Adapter\Support;

use Illuminate\Http\Request;
use Statamic\Facades\Collection;
use Statamic\Fields\Field;

class Targets
{
    /** Field types the AI may fill in, and handles the adapter already handles itself. */
    public const TYPES = ['text', 'textarea', 'markdown', 'select', 'radio', 'button_group', 'integer', 'toggle'];

    public const RESERVED = ['id', 'title', 'slug', 'date', 'status', 'content', 'excerpt'];

    public static function authorize(Request $request): void
    {
        $token = (string) Options::get('token');
        abort_if($token === '' || ! hash_equals($token, (string) $request->bearerToken()), 401, 'Invalid token.');
    }

    /** Collections PressCrew may publish to: handle => ['blueprint' => ..., 'fields' => [handle, ...]]. */
    public static function all(): array
    {
        $config = config('presscrew-adapter');
        $saved = Options::saved();

        // Control Panel settings: chosen collections (skipping deleted ones), with blueprint and fields for each.
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

    /** Options [['value', 'label']] of choice fields, null for other types. */
    public static function options(Field $field): ?array
    {
        if (! in_array($field->type(), ['select', 'radio', 'button_group'], true)) {
            return null;
        }

        $result = [];

        // Statamic stores options as [value => label], as a plain list, or as a list of ['key', 'value'].
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
