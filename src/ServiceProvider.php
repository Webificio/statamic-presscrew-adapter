<?php

namespace PressCrew\Adapter;

use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Support\Facades\Route;
use PressCrew\Adapter\Http\Controllers\PublishController;
use Statamic\Providers\AddonServiceProvider;

class ServiceProvider extends AddonServiceProvider
{
    public function bootAddon()
    {
        $this->mergeConfigFrom(__DIR__.'/../config/presscrew-adapter.php', 'presscrew-adapter');

        $this->publishes([
            __DIR__.'/../config/presscrew-adapter.php' => config_path('presscrew-adapter.php'),
        ], 'presscrew-adapter-config');

        if (! config('presscrew-adapter.enabled')) {
            return;
        }

        Route::middleware(['web', 'throttle:'.config('presscrew-adapter.throttle')])
            ->withoutMiddleware(PreventRequestForgery::class)
            ->post(config('presscrew-adapter.route'), PublishController::class)
            ->name('presscrew-adapter.publish');
    }
}
