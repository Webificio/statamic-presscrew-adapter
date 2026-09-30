<?php

namespace PressCrew\Adapter\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Statamic\Facades\Entry;

class PublishController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $config = config('presscrew-adapter');

        $token = (string) $config['token'];
        abort_if($token === '' || ! hash_equals($token, (string) $request->bearerToken()), 401, 'Token non valido.');

        $rules = array_merge($config['fields'], [
            'title' => ['required', 'string', 'max:200'],
            'content' => ['required', 'string'],
            'slug' => ['nullable', 'string', 'max:200'],
            'date' => ['nullable', 'date'],
            'status' => ['required', 'in:draft,published'],
        ]);

        if ($config['categories'] !== [] && isset($rules['category'])) {
            $rules['category'] = ['nullable', Rule::in($config['categories'])];
        }

        $data = $request->validate($rules);

        $slug = Str::slug($data['slug'] ?? $data['title']);
        $entry = Entry::query()->where('collection', $config['collection'])->where('slug', $slug)->first();
        $created = $entry === null;

        $entry ??= Entry::make()->collection($config['collection'])->blueprint($config['blueprint'])->slug($slug);

        if ($created || isset($data['date'])) {
            $entry->date(Carbon::parse($data['date'] ?? now($config['timezone']))->format('Y-m-d'));
        }

        $fields = collect($data)
            ->except(['slug', 'date', 'status'])
            ->filter(fn ($value) => $value !== null);

        $entry->merge($fields->all())->published($data['status'] === 'published')->save();

        return response()->json([
            'id' => $entry->id(),
            'slug' => $entry->slug(),
            'status' => $entry->published() ? 'published' : 'draft',
            'url' => $entry->published() ? url($entry->url()) : null,
            'cp_url' => $entry->editUrl(),
        ], $created ? 201 : 200);
    }
}
