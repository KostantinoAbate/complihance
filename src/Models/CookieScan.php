<?php

namespace KostantinoAbate\Complihance\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $uuid
 * @property array $urls
 * @property array|null $options
 * @property string $status
 * @property array|null $summary
 * @property Carbon|null $started_at
 * @property Carbon|null $finished_at
 */
class CookieScan extends Model
{
    protected $table = 'complihance_cookie_scans';

    protected $guarded = [];

    protected $casts = [
        'urls' => 'array',
        'options' => 'array',
        'summary' => 'array',
        'started_at' => 'datetime',
        'finished_at' => 'datetime',
    ];

    /**
     * Get the results collected during this scan.
     *
     * @return HasMany<CookieScanResult, $this>
     */
    public function results(): HasMany
    {
        return $this->hasMany(CookieScanResult::class, 'scan_id');
    }
}
