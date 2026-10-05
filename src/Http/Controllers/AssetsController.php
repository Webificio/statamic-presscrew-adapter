<?php

namespace PressCrew\Adapter\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use PressCrew\Adapter\Support\Options;
use PressCrew\Adapter\Support\Targets;
use Statamic\Assets\Asset as AssetModel;
use Statamic\Contracts\Assets\AssetContainer as Container;
use Statamic\Facades\Asset;
use Statamic\Facades\AssetContainer;

/** Media library of the site for PressCrew: list the images and upload new ones. */
class AssetsController extends Controller
{
    private const EXTENSIONS = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

    private const PER_PAGE = 24;

    public function index(Request $request): JsonResponse
    {
        Targets::authorize($request);

        $container = $this->container();
        $search = mb_strtolower(trim((string) $request->query('search', '')));
        $page = max(1, (int) $request->query('page', 1));

        $assets = $container->assets('/', true)
            ->filter(fn (AssetModel $a) => in_array(strtolower($a->extension()), self::EXTENSIONS, true))
            ->filter(fn (AssetModel $a) => $search === '' || str_contains(mb_strtolower($a->path()), $search))
            ->sortByDesc(fn (AssetModel $a) => $a->lastModified())
            ->forPage($page, self::PER_PAGE)
            ->map(fn (AssetModel $a) => $this->payload($a))
            ->values();

        return response()->json(['data' => $assets]);
    }

    public function store(Request $request): JsonResponse
    {
        Targets::authorize($request);

        $data = $request->validate([
            'file' => ['required', 'file', 'mimes:'.implode(',', self::EXTENSIONS), 'max:8192'],
            'name' => ['required', 'regex:/^[a-z0-9][a-z0-9-]{0,99}$/'],
            'alt' => ['nullable', 'string', 'max:500'],
            'caption' => ['nullable', 'string', 'max:2000'],
        ]);

        $container = $this->container();
        $file = $request->file('file');
        $extension = strtolower($file->guessExtension() ?: $file->getClientOriginalExtension());
        $path = trim((string) Options::get('asset_folder'), '/');
        $path = ($path === '' ? '' : $path.'/').$data['name'].'.'.($extension === 'jpeg' ? 'jpg' : $extension);

        // The name carries a hash of the image: the same path means a retry, so the asset is reused.
        $asset = $container->asset($path);
        $created = $asset === null;

        if ($created) {
            $asset = Asset::make()->container($container->handle())->path($path);
            $asset->upload($file);
        }

        foreach (['alt', 'caption'] as $key) {
            if (($data[$key] ?? '') !== '' && ! $asset->get($key)) {
                $asset->set($key, $data[$key]);
            }
        }
        $asset->save();

        return response()->json($this->payload($asset), $created ? 201 : 200);
    }

    private function container(): Container
    {
        $handle = (string) Options::get('asset_container');
        $container = AssetContainer::findByHandle($handle);
        abort_if($container === null, 422, "Asset container «{$handle}» not found.");

        return $container;
    }

    private function payload(AssetModel $asset): array
    {
        return [
            'id' => $asset->id(),
            'url' => $asset->absoluteUrl(),
            'title' => $asset->basename(),
            'alt' => $asset->get('alt'),
            'caption' => $asset->get('caption'),
            'width' => $asset->width(),
            'height' => $asset->height(),
        ];
    }
}
