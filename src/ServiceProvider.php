<?php

namespace PressCrew\Adapter;

use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Support\Facades\Route;
use PressCrew\Adapter\Http\Controllers\PublishController;
use PressCrew\Adapter\Http\Controllers\SchemaController;
use PressCrew\Adapter\Support\Options;
use PressCrew\Adapter\Support\SettingsBlueprint;
use Statamic\Providers\AddonServiceProvider;

class ServiceProvider extends AddonServiceProvider
{
    public function bootAddon()
    {
        $this->mergeConfigFrom(__DIR__.'/../config/presscrew-adapter.php', 'presscrew-adapter');

        $this->publishes([
            __DIR__.'/../config/presscrew-adapter.php' => config_path('presscrew-adapter.php'),
        ], 'presscrew-adapter-config');

        // Pagina impostazioni nel CP (Statamic 6.30+): i valori salvati prevalgono sul file di configurazione.
        $this->registerSettingsBlueprint(fn () => SettingsBlueprint::build());

        if (! Options::get('enabled')) {
            return;
        }

        Route::middleware(['web', 'throttle:'.Options::get('throttle')])
            ->withoutMiddleware(PreventRequestForgery::class)
            ->post(Options::get('route'), PublishController::class)
            ->name('presscrew-adapter.publish');

        Route::middleware(['web', 'throttle:'.Options::get('throttle')])
            ->get(trim(Options::get('route'), '/').'/schema', SchemaController::class)
            ->name('presscrew-adapter.schema');
    }
}
