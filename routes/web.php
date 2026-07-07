<?php

use Illuminate\Support\Facades\Route;

Route::prefix(config('complihance.routes.prefix', 'complihance'))
    ->middleware(config('complihance.routes.middleware', ['web']))
    ->name('complihance.')
    ->group(function () {
        // Feature UI
    });
