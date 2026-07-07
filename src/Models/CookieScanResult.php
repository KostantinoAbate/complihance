<?php

namespace KostantinoAbate\Complihance\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $scan_id
 * @property string $identity_hash
 * @property string $type
 * @property string|null $key
 * @property string|null $value_preview
 * @property string|null $name
 * @property string|null $domain
 * @property string|null $path
 * @property string|null $url
 * @property string|null $vendor
 * @property string|null $category
 * @property bool $secure
 * @property bool $http_only
 * @property string|null $same_site
 * @property Carbon|null $expires_at
 * @property array|null $metadata
 */
class CookieScanResult extends Model
{
    protected $table = 'complihance_cookie_scan_results';

    protected $guarded = [];

    protected $casts = [
        'secure' => 'boolean',
        'http_only' => 'boolean',
        'expires_at' => 'datetime',
        'metadata' => 'array',
    ];

    /**
     * Get the scan associated with this result.
     */
    public function scan(): BelongsTo
    {
        return $this->belongsTo(CookieScan::class, 'scan_id');
    }
}
