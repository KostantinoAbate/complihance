<?php

namespace KostantinoAbate\Complihance\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Consent extends Model
{
    use HasUuids;

    protected $table = 'complihance_consents';

    protected $fillable = [
        'consent_uuid',
        'subject_type',
        'subject_id',
        'session_id',
        'anonymous_id',
        'source',
        'accepted_categories',
        'rejected_categories',
        'vendors',
        'policy_version',
        'cookie_configuration_version',
        'ip_address',
        'user_agent',
        'accepted_at',
        'revoked_at',
    ];

    protected $casts = [
        'accepted_categories' => 'array',
        'rejected_categories' => 'array',
        'vendors' => 'array',
        'accepted_at' => 'datetime',
        'revoked_at' => 'datetime',
    ];

    public function uniqueIds(): array
    {
        return ['consent_uuid'];
    }

    public function subject(): MorphTo
    {
        return $this->morphTo();
    }

    public function expiresAt(): ?Carbon
    {
        if (! $this->accepted_at) {
            return null;
        }

        return $this->accepted_at->copy()->addMonths(
            config('complihance.retention.consent_retention_months', 12)
        );
    }

    public function isExpired(): bool
    {
        $expiresAt = $this->expiresAt();

        return $expiresAt !== null && now()->greaterThanOrEqualTo($expiresAt);
    }

    public function scopeExpired($query)
    {
        $months = config('complihance.retention.consent_retention_months', 12);

        return $query
            ->whereNotNull('accepted_at')
            ->where('accepted_at', '<=', now()->subMonths($months))
            ->where(function ($q) {
                $q->whereNotNull('subject_id')
                    ->orWhereNotNull('session_id')
                    ->orWhereNotNull('anonymous_id')
                    ->orWhereNotNull('ip_address')
                    ->orWhereNotNull('user_agent');
            });
    }

    public function anonymizeForRetention(): bool
    {
        return $this->forceFill([
            'subject_type' => null,
            'subject_id' => null,
            'session_id' => null,
            'anonymous_id' => null,
            'ip_address' => null,
            'user_agent' => null,
        ])->save();
    }

    public function isAnonymized(): bool
    {
        return $this->subject_id === null
            && $this->session_id === null
            && $this->anonymous_id === null
            && $this->ip_address === null
            && $this->user_agent === null;
    }
}
