<?php

use Illuminate\Support\Facades\Route;
use KostantinoAbate\Complihance\Http\Controllers\Api\ConfigurationApiController;
use KostantinoAbate\Complihance\Http\Controllers\Api\ConsentApiController;

Route::prefix(config('complihance.routes.api_prefix', 'complihance/api'))
    ->name('complihance.api.')
    ->middleware(config('complihance.routes.api_middleware', ['web']))
    ->group(function () {
        Route::get('/consent', [ConsentApiController::class, 'show'])->name('consent.show');
        Route::post('/consent', [ConsentApiController::class, 'store'])->name('consent.store');
        Route::patch('/consent', [ConsentApiController::class, 'update'])->name('consent.update');
        Route::delete('/consent', [ConsentApiController::class, 'revoke'])->name('consent.revoke');
        Route::get('/consent/status', [ConsentApiController::class, 'status'])->name('consent.status');

        Route::get('/configuration', [ConfigurationApiController::class, 'show'])->name('configuration.show');
    });
