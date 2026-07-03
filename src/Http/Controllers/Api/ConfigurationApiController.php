<?php

namespace KostantinoAbate\Complihance\Http\Controllers\Api;

use Illuminate\Routing\Controller;
use KostantinoAbate\Complihance\Facades\ComplihancePolicy;
use KostantinoAbate\Complihance\Services\ComplihanceDataRepository;
use KostantinoAbate\Complihance\Support\GranularConsent;

class ConfigurationApiController extends Controller
{
    public function show(ComplihanceDataRepository $data)
    {
        return response()->json([
            'locale' => app()->getLocale(),

            'texts' => $data->texts(),

            'categories' => $data->categories(),

            'cookies' => $data->cookies(),

            'vendors' => config('complihance.granular_consent.enabled')
                ? $data->vendors()
                : [],

            'cookie_policy_version' =>
                ComplihancePolicy::currentVersion('cookie'),

            'cookie_configuration_version' =>
                config('complihance.cookie_configuration_version'),
        ]);
    }
}
