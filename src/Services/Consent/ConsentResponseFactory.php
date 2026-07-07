<?php

namespace KostantinoAbate\Complihance\Services\Consent;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cookie;
use KostantinoAbate\Complihance\Data\StoredConsentResult;
use KostantinoAbate\Complihance\Http\Resources\ConsentResource;

class ConsentResponseFactory
{
    /**
     * Create a response for the current consent state.
     */
    public function current(array $payload): JsonResponse
    {
        return response()->json(
            ConsentResource::current($payload)
        );
    }

    /**
     * Create a response for a stored consent and attach consent cookies.
     */
    public function stored(StoredConsentResult $result, int $status = 200): JsonResponse
    {
        return response()
            ->json(ConsentResource::stored($result->payload), $status)
            ->withCookie($result->consentCookie)
            ->withCookie($result->anonymousCookie);
    }

    /**
     * Create a response for consent revocation and forget consent cookies.
     */
    public function revoked(): JsonResponse
    {
        return response()
            ->json(ConsentResource::revoked())
            ->withCookie(Cookie::forget(config('complihance.cookie_name', 'complihance_consent')))
            ->withCookie(Cookie::forget(config('complihance.anonymous_cookie_name', 'complihance_anonymous_id')));
    }
}
