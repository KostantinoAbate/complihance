<?php

use Illuminate\Support\Facades\Route;
use KostantinoAbate\Complihance\Http\Controllers\ConsentController;

Route::prefix(config('complihance.routes.prefix', 'complihance'))
    ->middleware(config('complihance.routes.middleware', ['web']))
    ->name('complihance.')
    ->group(function () {
        Route::post('/consent', [ConsentController::class, 'store'])->name('consent.store');
    });
