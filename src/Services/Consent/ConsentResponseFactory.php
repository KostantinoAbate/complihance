<?php

namespace KostantinoAbate\Complihance\Services\Consent;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cookie;
use KostantinoAbate\Complihance\Data\StoredConsentResult;
use KostantinoAbate\Complihance\Http\Resources\ConsentResource;

class ConsentResponseFactory
{
    public function current(array $payload): JsonResponse
    {
        return response()->json(
            ConsentResource::current($payload)
        );
    }

    public function stored(StoredConsentResult $result, int $status = 200): JsonResponse
    {
        return response()
            ->json(ConsentResource::stored($result->payload), $status)
            ->withCookie($result->consentCookie)
            ->withCookie($result->anonymousCookie);
    }

    public function revoked(): JsonResponse
    {
        return response()
            ->json(ConsentResource::revoked())
            ->withCookie(Cookie::forget(config('complihance.cookie_name', 'complihance_consent')))
            ->withCookie(Cookie::forget(config('complihance.anonymous_cookie_name', 'complihance_anonymous_id')));
    }
}
