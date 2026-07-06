<?php

namespace KostantinoAbate\Complihance\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use KostantinoAbate\Complihance\Actions\ResolveCurrentConsentAction;
use KostantinoAbate\Complihance\Actions\RevokeConsentAction;
use KostantinoAbate\Complihance\Actions\StoreConsentAction;
use KostantinoAbate\Complihance\Actions\UpdateConsentAction;
use KostantinoAbate\Complihance\Services\ConsentResponseFactory;
use KostantinoAbate\Complihance\Support\ConsentPayloadBuilder;

class ConsentApiController extends Controller
{
    public function show(
        Request $request,
        ConsentPayloadBuilder $builder,
        ResolveCurrentConsentAction $resolveCurrentConsent,
        ConsentResponseFactory $responseFactory,
    ): JsonResponse {
        return $responseFactory->current(
            $builder->build(
                $resolveCurrentConsent->execute($request),
                $request,
            )
        );
    }

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

    public function update(
        Request $request,
        UpdateConsentAction $updateConsent,
        ConsentResponseFactory $responseFactory,
    ): JsonResponse {
        return $responseFactory->stored(
            result: $updateConsent->execute($request),
        );
    }

    public function revoke(
        Request $request,
        RevokeConsentAction $revokeConsent,
        ConsentResponseFactory $responseFactory,
    ): JsonResponse {
        $revokeConsent->execute($request);

        return $responseFactory->revoked();
    }

    private function currentConsentResponse(
        Request $request,
        ConsentPayloadBuilder $builder,
        ResolveCurrentConsentAction $resolveCurrentConsent,
        ConsentResponseFactory $responseFactory,
    ): JsonResponse {
        return $responseFactory->current(
            $builder->build(
                $resolveCurrentConsent->execute($request),
                $request,
            )
        );
    }
}
