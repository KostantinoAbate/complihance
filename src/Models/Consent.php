<?php

namespace KostantinoAbate\Complihance\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $consent_uuid
 * @property string|null $subject_type
 * @property int|string|null $subject_id
 * @property string|null $session_id
 * @property string|null $anonymous_id
 * @property string|null $source
 * @property array|null $accepted_categories
 * @property array|null $rejected_categories
 * @property array|null $vendors
 * @property string|null $policy_version
 * @property string|null $cookie_configuration_version
 * @property string|null $ip_address
 * @property string|null $user_agent
 * @property Carbon|null $accepted_at
 * @property Carbon|null $revoked_at
 */
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

    /**
     * Get the columns that should receive a unique identifier.
     *
     * @return array<int, string>
     */
    public function uniqueIds(): array
    {
        return ['consent_uuid'];
    }

    /**
     * Get the subject associated with this consent.
     */
    public function subject(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Calculate when this consent expires according to retention settings.
     */
    public function expiresAt(): ?Carbon
    {
        if (! $this->accepted_at) {
            return null;
        }

        return $this->accepted_at->copy()->addMonths(
            config('complihance.retention.consent_retention_months', 12)
        );
    }

    /**
     * Determine whether this consent is expired.
     *
     * @noinspection PhpUnused
     */
    public function isExpired(): bool
    {
        $expiresAt = $this->expiresAt();

        return $expiresAt !== null && now()->greaterThanOrEqualTo($expiresAt);
    }

    /**
     * Scope consents that are eligible for retention processing.
     *
     * @noinspection PhpUnused
     */
    public function scopeExpired(Builder $query): Builder
    {
        $months = config('complihance.retention.consent_retention_months', 12);

        return $query
            ->whereNotNull('accepted_at')
            ->where('accepted_at', '<=', now()->subMonths($months))
            ->where(function (Builder $query): void {
                $query
                    ->whereNotNull('subject_id')
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
            'subject_type' => null,
            'subject_id' => null,
            'session_id' => null,
            'anonymous_id' => null,
            'ip_address' => null,
            'user_agent' => null,
        ])->save();
    }

    /**
     * Determine whether personally identifiable data has already been removed.
     *
     * @noinspection PhpUnused
     */
    public function isAnonymized(): bool
    {
        return $this->subject_type === null
            && $this->subject_id === null
            && $this->session_id === null
            && $this->anonymous_id === null
            && $this->ip_address === null
            && $this->user_agent === null;
    }
}
