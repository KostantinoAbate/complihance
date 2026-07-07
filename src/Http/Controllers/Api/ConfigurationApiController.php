<?php

namespace KostantinoAbate\Complihance\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use KostantinoAbate\Complihance\Http\Resources\ConfigurationResource;
use KostantinoAbate\Complihance\Services\Consent\GranularConsent;
use KostantinoAbate\Complihance\Services\Rendering\ComplihanceDataRepository;

class ConfigurationApiController extends Controller
{
    /**
     * Return the frontend Complihance configuration.
     */
    public function show(ComplihanceDataRepository $data): JsonResponse
    {
        return response()->json(
            ConfigurationResource::make([
                'categories' => $data->categories(),
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
