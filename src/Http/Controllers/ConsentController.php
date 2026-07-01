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
                'saved' => true,
                'consent' => $result->payload,
            ])
            ->withCookie($result->consentCookie)
            ->withCookie($result->anonymousCookie);
    }
}
