<?php

namespace KostantinoAbate\Complihance\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Cookie;
use KostantinoAbate\Complihance\Actions\StoreConsentAction;
use KostantinoAbate\Complihance\Services\CurrentConsentResolver;
use KostantinoAbate\Complihance\Support\ConsentPayloadBuilder;

class ConsentApiController extends Controller
{
    public function show(
        Request $request,
        ConsentPayloadBuilder $builder,
        CurrentConsentResolver $resolver,
    ): JsonResponse {
        return response()->json(
            $builder->build(
                $resolver->resolve($request),
                $request,
            )
        );
    }

    public function status(
        Request $request,
        ConsentPayloadBuilder $builder,
        CurrentConsentResolver $resolver,
    ): JsonResponse {
        return response()->json(
            $builder->build(
                $resolver->resolve($request),
                $request,
            )
        );
    }

    public function store(
        Request $request,
        StoreConsentAction $action,
    ): JsonResponse {
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

    public function update(
        Request $request,
        StoreConsentAction $action,
        CurrentConsentResolver $resolver,
    ): JsonResponse {
        $currentConsent = $resolver->resolve($request);

        if ($currentConsent) {
            $currentConsent->update([
                'revoked_at' => now(),
            ]);
        }

        $result = $action->execute($request);

        return response()
            ->json([
                'has_consent' => true,
                'requires_renewal' => false,
                'consent' => $result->payload,
            ])
            ->withCookie($result->consentCookie)
            ->withCookie($result->anonymousCookie);
    }

    public function revoke(
        Request $request,
        CurrentConsentResolver $resolver,
    ): JsonResponse {
        $consent = $resolver->resolve($request);

        if ($consent) {
            $consent->update([
                'revoked_at' => now(),
            ]);
        }

        return response()
            ->json([
                'revoked' => true,
                'has_consent' => false,
                'requires_renewal' => true,
                'consent' => null,
            ])
            ->withCookie(Cookie::forget(
                config('complihance.cookie_name', 'complihance_consent')
            ))
            ->withCookie(Cookie::forget(
                config('complihance.anonymous_cookie_name', 'complihance_anonymous_id')
            ));
    }
}
