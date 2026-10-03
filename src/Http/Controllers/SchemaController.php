<?php

namespace PressCrew\Adapter\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use PressCrew\Adapter\Support\Targets;
use Statamic\Facades\Collection;
use Statamic\Fields\Field;

class SchemaController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        Targets::authorize($request);

        $collections = [];

        foreach (Targets::all() as $handle => $target) {
            $collection = Collection::findByHandle($handle);
            abort_if($collection === null, 500, "Collezione «{$handle}» non trovata.");

            $blueprint = $collection->entryBlueprint($target['blueprint']);
            abort_if($blueprint === null, 500, "Blueprint «{$target['blueprint']}» non trovato nella collezione «{$handle}».");

            $fields = collect($target['fields'])->map(fn ($name) => $blueprint->field($name))->filter();
            [$supported, $unsupported] = $fields->partition(fn (Field $field) => in_array($field->type(), Targets::TYPES, true));

            $collections[] = [
                'handle' => $handle,
                'title' => $collection->title(),
                'blueprint' => $target['blueprint'],
                'fields' => $supported->map(fn (Field $field) => $this->describe($field))->values(),
                'unsupported' => $unsupported->map(fn (Field $field) => $field->handle())->values(),
            ];
        }

        return response()->json(['collections' => $collections]);
    }

    private function describe(Field $field): array
    {
        $description = [
            'handle' => $field->handle(),
            'type' => $field->type(),
            'display' => $field->display(),
            'instructions' => $field->get('instructions'),
            'required' => $field->isRequired(),
            'max_length' => $field->get('character_limit') ?: null,
        ];

        if (($options = Targets::options($field)) !== null) {
            $description['options'] = $options;
        }

        return $description;
    }
}
