<?php

namespace KostantinoAbate\Complihance\Http\Controllers\Api;

use Illuminate\Routing\Controller;
use KostantinoAbate\Complihance\Facades\ComplihancePolicy;
use KostantinoAbate\Complihance\Support\GranularConsent;

class ConfigurationApiController extends Controller
{
    public function show()
    {
        return response()->json([
            'categories' => collect(config('complihance.categories', []))
                ->map(fn (array $category, string $key) => [
                    'key' => $key,
                    ...$category,
                ])
                ->values(),

            'vendors' => collect(GranularConsent::vendors())
                ->map(fn (array $vendor, string $key) => [
                    'key' => $key,
                    ...$vendor,
                ])
                ->values(),

            'cookie_policy_version' => ComplihancePolicy::currentVersion('cookie'),

            'cookie_configuration_version' => config(
                'complihance.cookie_configuration_version'
            ),
        ]);
    }
}
