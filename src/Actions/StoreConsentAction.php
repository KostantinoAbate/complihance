<?php

namespace KostantinoAbate\Complihance\Actions;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use KostantinoAbate\Complihance\DTO\StoredConsentResult;
use KostantinoAbate\Complihance\Services\ComplihanceDataRepository;
use KostantinoAbate\Complihance\Services\ConsentRecorder;
use KostantinoAbate\Complihance\Services\ConsentRequestContextResolver;
use KostantinoAbate\Complihance\Services\CurrentConsentResolver;
use KostantinoAbate\Complihance\Services\PolicyAcceptanceRecorder;
use KostantinoAbate\Complihance\Services\VendorConsentResolver;
use KostantinoAbate\Complihance\Support\ConsentSource;
use KostantinoAbate\Complihance\Support\GranularConsent;

class StoreConsentAction
{
    public function __construct(
        protected ComplihanceDataRepository $data,
        protected CurrentConsentResolver $currentConsentResolver,
        protected ConsentRequestContextResolver $contextResolver,
        protected VendorConsentResolver $vendorConsentResolver,
        protected ConsentRecorder $consentRecorder,
        protected PolicyAcceptanceRecorder $policyAcceptanceRecorder,
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

            $acceptedVendors = $this->vendorConsentResolver->resolve(
                categories: $acceptedCategories,
                vendors: array_key_exists('vendors', $data)
                    ? $data['vendors']
                    : $currentVendors,
            );
        }

        $rejectedCategories = collect($categoryKeys)
            ->diff($acceptedCategories)
            ->values()
            ->all();

        $context = $this->contextResolver->resolve($request);

        $consent = $this->consentRecorder->record(
            context: $context,
            acceptedCategories: $acceptedCategories,
            rejectedCategories: $rejectedCategories,
            acceptedVendors: $acceptedVendors,
            source: $source,
        );

        $this->policyAcceptanceRecorder->record(
            key: 'cookie',
            context: $context,
            consent: $consent,
            source: $source,
            metadata: [
                'accepted_categories' => $acceptedCategories,
                'rejected_categories' => $rejectedCategories,
                'vendors' => $acceptedVendors,
            ],
        );

        $payload = [
            'consent_uuid' => $consent->consent_uuid,
            'anonymous_id' => $context->anonymousId,
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
                secure: $context->isSecure,
                httpOnly: true,
                raw: false,
                sameSite: 'Lax',
            ),
            anonymousCookie: Cookie::make(
                name: config('complihance.anonymous_cookie_name', 'complihance_anonymous_id'),
                value: $context->anonymousId,
                minutes: config('complihance.cookie_lifetime', 60 * 24 * 180),
                path: '/',
                secure: $context->isSecure,
                httpOnly: true,
                raw: false,
                sameSite: 'Lax',
            ),
        );
    }
}
