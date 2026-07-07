<?php

namespace KostantinoAbate\Complihance\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $identity_hash
 * @property int|null $consent_id
 * @property string|null $subject_type
 * @property int|null $subject_id
 * @property string|null $session_id
 * @property string|null $anonymous_id
 * @property array|null $policy_key
 * @property array|null $policy_version
 * @property array|null $source
 * @property array|null $metadata
 * @property string|null $ip_address
 * @property string|null $user_agent
 * @property Carbon|null $accepted_at
 */
class ComplihancePolicyAcceptance extends Model
{
    protected $table = 'complihance_policy_acceptances';

    protected $guarded = [];

    protected $casts = [
        'metadata' => 'array',
        'accepted_at' => 'datetime',
    ];

    /**
     * Get the subject that accepted the policy.
     */
    public function subject(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Scope policy acceptances that are eligible for retention processing.
     *
     * @noinspection PhpUnused
     */
    public function scopeExpired(Builder $query): Builder
    {
        $months = config(
            'complihance.retention.policy_acceptance_retention_months',
            config('complihance.retention.consent_retention_months', 12)
        );

        return $query
            ->whereNotNull('accepted_at')
            ->where('accepted_at', '<=', now()->subMonths($months))
            ->where(function (Builder $query): void {
                $query
                    ->whereNotNull('subject_type')
                    ->orWhereNotNull('subject_id')
                    ->orWhereNotNull('session_id')
                    ->orWhereNotNull('anonymous_id')
                    ->orWhereNotNull('ip_address')
                    ->orWhereNotNull('user_agent');
            });
    }

    /**
     * Remove personally identifiable data while keeping audit metadata.
     *
     * @noinspection PhpUnused
     */
    public function anonymizeForRetention(): bool
    {
        return $this->forceFill([
            'identity_hash' => hash('sha256', 'anonymized|'.$this->getKey().'|'.now()->timestamp),
            'subject_type' => null,
            'subject_id' => null,
            'session_id' => null,
            'anonymous_id' => null,
            'ip_address' => null,
            'user_agent' => null,
        ])->save();
    }
}
