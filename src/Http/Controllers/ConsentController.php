<?php

namespace KostantinoAbate\Complihance\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use KostantinoAbate\Complihance\Actions\StoreConsentAction;

class ConsentController extends Controller
{
    public function store(Request $request, StoreConsentAction $action): JsonResponse
    {
        $result = $action->execute($request);

        return response()
            ->json([
                'has_consent' => true,
                'requires_renewal' => false,
                'consent' => $result->payload,
            ], 201)
            ->withCookie($result->consentCookie)
            ->withCookie($result->anonymousCookie);
    }
}
