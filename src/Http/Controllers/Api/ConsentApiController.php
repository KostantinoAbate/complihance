<?php

namespace KostantinoAbate\Complihance\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use KostantinoAbate\Complihance\Actions\StoreConsentAction;
use KostantinoAbate\Complihance\Models\Consent;
use KostantinoAbate\Complihance\Support\ConsentPayloadBuilder;

class ConsentApiController extends Controller
{
    public function show(Request $request, ConsentPayloadBuilder $builder): JsonResponse
    {
        $consent = $this->currentConsent($request);

        return response()->json($builder->build($consent, $request));
    }

    public function status(Request $request, ConsentPayloadBuilder $builder): JsonResponse
    {
        $consent = $this->currentConsent($request);

        return response()->json($builder->build($consent, $request));
    }

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

    public function update(Request $request, StoreConsentAction $action): JsonResponse
    {
        $currentConsent = $this->currentConsent($request);

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

    public function revoke(Request $request): JsonResponse
    {
        $consent = $this->currentConsent($request);

        if ($consent) {
            $consent->update([
                'revoked_at' => now(),
            ]);
        }

        return response()->json([
            'revoked' => true,
        ]);
    }

    protected function currentConsent(Request $request): ?Consent
    {
        $cookieName = config('complihance.cookie_name', 'complihance_consent');
        $cookie = $request->cookie($cookieName);

        if ($cookie) {
            $decoded = json_decode($cookie, true);

            if (is_array($decoded) && ! empty($decoded['consent_uuid'])) {
                $consent = Consent::query()
                    ->whereNull('revoked_at')
                    ->where('consent_uuid', $decoded['consent_uuid'])
                    ->first();

                if ($consent) {
                    return $consent;
                }
            }
        }

        return Consent::query()
            ->whereNull('revoked_at')
            ->when(auth()->check(), function ($query) {
                $query->whereMorphedTo('subject', auth()->user());
            })
            ->when(! auth()->check(), function ($query) use ($request) {
                $query->where('session_id', $request->hasSession() ? $request->session()->getId() : null);
            })
            ->latest('accepted_at')
            ->first();
    }

    protected function rejectedCategories(array $acceptedCategories): array
    {
        $configuredCategories = collect(config('complihance.categories', []))
            ->pluck('key')
            ->filter()
            ->values()
            ->all();

        return array_values(array_diff($configuredCategories, $acceptedCategories));
    }
}
