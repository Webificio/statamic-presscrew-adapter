<?php

namespace PressCrew\Adapter\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use PressCrew\Adapter\Support\Targets;
use Statamic\Facades\Collection;
use Statamic\Facades\Entry;

class PublishController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $config = config('presscrew-adapter');

        Targets::authorize($request);

        $targets = Targets::all();

        $rules = array_merge($config['fields'], [
            'title' => ['required', 'string', 'max:200'],
            'content' => ['required', 'string'],
            'slug' => ['nullable', 'string', 'max:200'],
            'date' => ['nullable', 'date'],
            'status' => ['required', 'in:draft,published'],
            'collection' => ['nullable', Rule::in(array_keys($targets))],
            'fields' => ['nullable', 'array'],
        ]);

        if ($config['categories'] !== [] && isset($rules['category'])) {
            $rules['category'] = ['nullable', Rule::in($config['categories'])];
        }

        $data = $request->validate($rules);

        $collection = $data['collection'] ?? array_key_first($targets);
        $target = $targets[$collection];
        $custom = $this->customFields($collection, $target, $data['fields'] ?? []);

        $slug = Str::slug($data['slug'] ?? $data['title']);
        $entry = Entry::query()->where('collection', $collection)->where('slug', $slug)->first();
        $created = $entry === null;

        $entry ??= Entry::make()->collection($collection)->blueprint($target['blueprint'])->slug($slug);

        if ($created || isset($data['date'])) {
            $entry->date(Carbon::parse($data['date'] ?? now($config['timezone']))->format('Y-m-d'));
        }

        $fields = collect($data)
            ->except(['slug', 'date', 'status', 'collection', 'fields'])
            ->filter(fn ($value) => $value !== null);

        $entry->merge($fields->merge($custom)->all())->published($data['status'] === 'published')->save();

        return response()->json([
            'id' => $entry->id(),
            'slug' => $entry->slug(),
            'status' => $entry->published() ? 'published' : 'draft',
            'url' => $entry->published() ? url($entry->url()) : null,
            'cp_url' => $entry->editUrl(),
        ], $created ? 201 : 200);
    }

    /** Valida i campi inviati da PressCrew con le regole del blueprint e li converte nel formato di salvataggio. */
    private function customFields(string $collection, array $target, array $values): array
    {
        $unknown = array_diff(array_keys($values), $target['fields']);
        if ($unknown !== []) {
            throw ValidationException::withMessages(['fields' => ['Campi non abilitati: '.implode(', ', $unknown).'.']]);
        }

        if ($values === []) {
            return [];
        }

        $blueprint = Collection::findByHandle($collection)->entryBlueprint($target['blueprint']);
        abort_if($blueprint === null, 500, "Blueprint «{$target['blueprint']}» non trovato.");

        $fields = $blueprint->fields()->only(array_keys($values))->addValues($values);
        $fields->validate();

        // Statamic non controlla né le opzioni delle select né character_limit (solo un contatore nell'editor).
        $errors = [];
        foreach ($values as $handle => $value) {
            $field = $blueprint->field($handle);
            $options = Targets::options($field);
            if ($options !== null && ! in_array((string) $value, array_column($options, 'value'), true)) {
                $errors["fields.$handle"] = ["Valore non ammesso per «{$handle}»: scegli tra ".implode(', ', array_column($options, 'value')).'.'];
            }
            if (($max = $field->get('character_limit')) && mb_strlen((string) $value) > $max) {
                $errors["fields.$handle"] = ["«{$handle}» supera i {$max} caratteri."];
            }
        }
        if ($errors !== []) {
            throw ValidationException::withMessages($errors);
        }

        return $fields->process()->values()->all();
    }
}
