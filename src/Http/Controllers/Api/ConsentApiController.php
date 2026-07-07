<?php

namespace KostantinoAbate\Complihance\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use JsonException;
use KostantinoAbate\Complihance\Actions\Consent\ResolveCurrentConsentAction;
use KostantinoAbate\Complihance\Actions\Consent\RevokeConsentAction;
use KostantinoAbate\Complihance\Actions\Consent\StoreConsentAction;
use KostantinoAbate\Complihance\Actions\Consent\UpdateConsentAction;
use KostantinoAbate\Complihance\Services\Consent\ConsentPayloadBuilder;
use KostantinoAbate\Complihance\Services\Consent\ConsentResponseFactory;

class ConsentApiController extends Controller
{
    /**
     * Return the current consent state.
     */
    public function show(
        Request $request,
        ConsentPayloadBuilder $builder,
        ResolveCurrentConsentAction $resolveCurrentConsent,
        ConsentResponseFactory $responseFactory,
    ): JsonResponse {
        return $this->currentConsentResponse(
            $request,
            $builder,
            $resolveCurrentConsent,
            $responseFactory,
        );
    }

    /**
     * Return the current consent status.
     */
    public function status(
        Request $request,
        ConsentPayloadBuilder $builder,
        ResolveCurrentConsentAction $resolveCurrentConsent,
        ConsentResponseFactory $responseFactory,
    ): JsonResponse {
        return $this->currentConsentResponse(
            $request,
            $builder,
            $resolveCurrentConsent,
            $responseFactory,
        );
    }

    /**
     * Store a new consent record.
     *
     * @throws JsonException
     */
    public function store(
        Request $request,
        StoreConsentAction $storeConsent,
        ConsentResponseFactory $responseFactory,
    ): JsonResponse {
        return $responseFactory->stored(
            result: $storeConsent->execute($request),
            status: 201,
        );
    }

    /**
     * Update the current consent record.
     *
     * @throws JsonException
     */
    public function update(
        Request $request,
        UpdateConsentAction $updateConsent,
        ConsentResponseFactory $responseFactory,
    ): JsonResponse {
        return $responseFactory->stored(
            result: $updateConsent->execute($request),
        );
    }

    /**
     * Revoke the current consent record.
     */
    public function revoke(
        Request $request,
        RevokeConsentAction $revokeConsent,
        ConsentResponseFactory $responseFactory,
    ): JsonResponse {
        $revokeConsent->execute($request);

        return $responseFactory->revoked();
    }

    /**
     * Build the current consent JSON response.
     */
    private function currentConsentResponse(
        Request $request,
        ConsentPayloadBuilder $builder,
        ResolveCurrentConsentAction $resolveCurrentConsent,
        ConsentResponseFactory $responseFactory,
    ): JsonResponse {
        return $responseFactory->current(
            $builder->build(
                $resolveCurrentConsent->execute($request)
            )
        );
    }
}
