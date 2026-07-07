<?php

namespace KostantinoAbate\Complihance\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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

    public function scan(): BelongsTo
    {
        return $this->belongsTo(CookieScan::class, 'scan_id');
    }
}
