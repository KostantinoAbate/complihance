<?php

namespace KostantinoAbate\Complihance\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class ComplihancePolicyAcceptance extends Model
{
    protected $table = 'complihance_policy_acceptances';

    protected $guarded = [];

    protected $casts = [
        'metadata' => 'array',
        'accepted_at' => 'datetime',
    ];

    public function subject(): MorphTo
    {
        return $this->morphTo();
    }

    public function scopeExpired($query)
    {
        $months = config(
            'complihance.retention.policy_acceptance_retention_months',
            config('complihance.retention.consent_retention_months', 12)
        );

        return $query
            ->whereNotNull('accepted_at')
            ->where('accepted_at', '<=', now()->subMonths($months))
            ->where(function ($query) {
                $query
                    ->whereNotNull('subject_type')
                    ->orWhereNotNull('subject_id')
                    ->orWhereNotNull('session_id')
                    ->orWhereNotNull('anonymous_id')
                    ->orWhereNotNull('ip_address')
                    ->orWhereNotNull('user_agent');
            });
    }

    public function anonymizeForRetention(): bool
    {
        return $this->forceFill([
            'identity_hash' => hash('sha256', 'anonymized|' . $this->getKey() . '|' . now()->timestamp),
            'subject_type' => null,
            'subject_id' => null,
            'session_id' => null,
            'anonymous_id' => null,
            'ip_address' => null,
            'user_agent' => null,
        ])->save();
    }
}
