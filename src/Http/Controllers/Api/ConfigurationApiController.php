<?php

namespace KostantinoAbate\Complihance\Http\Controllers\Api;

use Illuminate\Routing\Controller;
use KostantinoAbate\Complihance\Http\Resources\ConfigurationResource;
use KostantinoAbate\Complihance\Services\ComplihanceDataRepository;
use KostantinoAbate\Complihance\Support\GranularConsent;

class ConfigurationApiController extends Controller
{
    public function show(ComplihanceDataRepository $data)
    {
        return response()->json(
            ConfigurationResource::make([
                'categories' => config('complihance.categories', []),
                'vendors' => GranularConsent::vendors(),
                'granular_consent' => [
                    'enabled' => config('complihance.granular_consent.enabled', false),
                ],
                'consent_mode' => [
                    'enabled' => config('complihance.consent_mode.enabled', true),
                ],
                'policy' => [
                    'version' => config('complihance.policies.cookie.version'),
                ],

                'cookies' => [
                    'version' => config('complihance.cookie_configuration_version'),
                ],
            ])
        );
    }
}
