<?php

namespace KostantinoAbate\Complihance\Http\Resources;

class ConsentResource
{
    /**
     * Transform the current consent state.
     *
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    public static function current(array $payload): array
    {
        return [
            'has_consent' => (bool) ($payload['has_consent'] ?? false),
            'requires_renewal' => (bool) ($payload['requires_renewal'] ?? false),
            'consent' => self::consent($payload['consent'] ?? null),
        ];
    }

    /**
     * Transform a newly stored consent payload.
     *
     * @param  array<string, mixed>  $consent
     * @return array<string, mixed>
     */
    public static function stored(array $consent): array
    {
        return [
            'has_consent' => true,
            'requires_renewal' => false,
            'consent' => self::consent($consent),
        ];
    }

    /**
     * Transform a revoked consent response.
     *
     * @return array<string, mixed>
     */
    public static function revoked(): array
    {
        return [
            'revoked' => true,
            'has_consent' => false,
            'requires_renewal' => true,
            'consent' => null,
        ];
    }

    /**
     * Transform a consent payload.
     *
     * @param  array<string, mixed>|null  $consent
     * @return array<string, mixed>|null
     */
    public static function consent(?array $consent): ?array
    {
        if ($consent === null) {
            return null;
        }

        return [
            'uuid' => $consent['uuid'] ?? $consent['consent_uuid'] ?? null,
            'anonymous_id' => $consent['anonymous_id'] ?? null,
            'accepted_categories' => $consent['accepted_categories'] ?? [],
            'rejected_categories' => $consent['rejected_categories'] ?? [],
            'vendors' => $consent['vendors'] ?? [],
            'policy_version' => $consent['policy_version'] ?? null,
            'cookie_configuration_version' => $consent['cookie_configuration_version'] ?? null,
            'expires_at' => $consent['expires_at'] ?? null,
            'created_at' => $consent['created_at'] ?? null,
            'updated_at' => $consent['updated_at'] ?? null,
        ];
    }
}
