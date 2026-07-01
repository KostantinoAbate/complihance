<?php

namespace KostantinoAbate\Complihance\Http\Controllers\Api;

use Illuminate\Routing\Controller;

class ConfigurationApiController extends Controller
{
    public function show()
    {
        return response()->json([
            'categories' => config('complihance.categories', []),
            'vendors' => config('complihance.vendors', []),
            'policy_version' => config('complihance.policy.version'),
            'cookie_configuration_version' => config('complihance.cookies.version'),
        ]);
    }
}
