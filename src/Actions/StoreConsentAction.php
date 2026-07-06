<?php

namespace KostantinoAbate\Complihance\Actions;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Str;
use KostantinoAbate\Complihance\DTO\StoredConsentResult;
use KostantinoAbate\Complihance\Facades\ComplihancePolicy;
use KostantinoAbate\Complihance\Models\Consent;
use KostantinoAbate\Complihance\Services\ComplihanceDataRepository;
use KostantinoAbate\Complihance\Services\CurrentConsentResolver;
use KostantinoAbate\Complihance\Services\VendorConsentResolver;
use KostantinoAbate\Complihance\Support\ConsentSource;
use KostantinoAbate\Complihance\Support\GranularConsent;

class StoreConsentAction
{
    public function __construct(
        protected ComplihanceDataRepository $data,
        protected CurrentConsentResolver $currentConsentResolver,
    ) {}

    public function execute(Request $request): StoredConsentResult
    {
        $configuredCategories = collect($this->data->rawCategories())
            ->filter(fn ($category) => ($category['enabled'] ?? true))
            ->all();

        $categoryKeys = array_keys($configuredCategories);

        $data = $request->validate([
            'categories' => ['required', 'array'],
            'categories.*' => ['string', 'in:'.implode(',', $categoryKeys)],
            'vendors' => ['sometimes', 'array'],
            'vendors.*' => ['string'],
            'source' => ['sometimes', 'nullable', 'string'],
        ]);

        $source = ConsentSource::normalize($data['source'] ?? null);

        $acceptedCategories = collect($data['categories'])
            ->unique()
            ->values();

        foreach ($configuredCategories as $key => $category) {
            if (($category['required'] ?? false) === true) {
                $acceptedCategories->push($key);
            }
        }

        $acceptedCategories = $acceptedCategories
            ->unique()
            ->values()
            ->all();

        $acceptedVendors = [];

        if (GranularConsent::enabled()) {
            $currentConsent = $this->currentConsentResolver->resolve($request);
            $currentVendors = $currentConsent?->vendors ?? [];

            $acceptedVendors = app(VendorConsentResolver::class)->resolve(
                categories: $acceptedCategories,
                vendors: array_key_exists('vendors', $data)
                    ? $data['vendors']
                    : $currentVendors
            );
        }

        $rejectedCategories = collect($categoryKeys)
            ->diff($acceptedCategories)
            ->values()
            ->all();

        $sessionId = $request->hasSession() ? $request->session()->getId() : null;

        $anonymousId = $request->cookie(
            config('complihance.anonymous_cookie_name', 'complihance_anonymous_id')
        ) ?? (string) Str::uuid();

        $ipAddress = $request->ip();
        $userAgent = $request->userAgent();

        $consentData = [
            'session_id' => $sessionId,
            'anonymous_id' => $anonymousId,

            'subject_type' => auth()->check() ? auth()->user()::class : null,
            'subject_id' => auth()->check() ? auth()->id() : null,

            'accepted_categories' => $acceptedCategories,
            'rejected_categories' => $rejectedCategories,

            'policy_version' => ComplihancePolicy::currentVersion('cookie'),
            'cookie_configuration_version' => config('complihance.cookie_configuration_version'),

            'ip_address' => $ipAddress,
            'user_agent' => $userAgent,

            'source' => $source,
            'accepted_at' => now(),
        ];

        if (GranularConsent::enabled()) {
            $consentData['vendors'] = $acceptedVendors;
        }

        $consent = Consent::create($consentData);

        ComplihancePolicy::accept(
            key: 'cookie',
            subject: auth()->check() ? auth()->user() : null,
            source: $source,
            consentId: $consent->id,
            anonymousId: $anonymousId,
            sessionId: $sessionId,
            ipAddress: $ipAddress,
            userAgent: $userAgent,
            metadata: [
                'accepted_categories' => $acceptedCategories,
                'rejected_categories' => $rejectedCategories,
                'vendors' => $acceptedVendors,
            ],
        );

        $payload = [
            'consent_uuid' => $consent->consent_uuid,
            'anonymous_id' => $anonymousId,
            'accepted_categories' => $acceptedCategories,
            'rejected_categories' => $rejectedCategories,
            'policy_version' => $consent->policy_version,
            'cookie_configuration_version' => $consent->cookie_configuration_version,
            'source' => $source,
            'accepted_at' => $consent->accepted_at?->toISOString(),
        ];

        if (GranularConsent::enabled()) {
            $payload['vendors'] = $acceptedVendors;
        }

        return new StoredConsentResult(
            payload: $payload,
            consentCookie: Cookie::make(
                name: config('complihance.cookie_name', 'complihance_consent'),
                value: json_encode($payload),
                minutes: config('complihance.cookie_lifetime', 60 * 24 * 180),
                path: '/',
                secure: $request->isSecure(),
                httpOnly: false,
                raw: false,
                sameSite: 'Lax',
            ),
            anonymousCookie: Cookie::make(
                name: config('complihance.anonymous_cookie_name', 'complihance_anonymous_id'),
                value: $anonymousId,
                minutes: config('complihance.cookie_lifetime', 60 * 24 * 180),
                path: '/',
                secure: $request->isSecure(),
                httpOnly: true,
                raw: false,
                sameSite: 'Lax',
            ),
        );
    }
}
