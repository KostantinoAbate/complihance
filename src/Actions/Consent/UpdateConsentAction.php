<?php

namespace KostantinoAbate\Complihance\Actions\Consent;

use Illuminate\Http\Request;
use KostantinoAbate\Complihance\Data\StoredConsentResult;

class UpdateConsentAction
{
    public function __construct(
        protected ResolveCurrentConsentAction $resolveCurrentConsent,
        protected StoreConsentAction $storeConsent,
    ) {}

    public function execute(Request $request): StoredConsentResult
    {
        $currentConsent = $this->resolveCurrentConsent->execute($request);

        $result = $this->storeConsent->execute($request);

        $currentConsent?->update([
            'revoked_at' => now(),
        ]);

        return $result;
    }
}
