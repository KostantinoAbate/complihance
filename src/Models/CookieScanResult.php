<?php

namespace KostantinoAbate\Complihance\Models;

use Illuminate\Database\Eloquent\Model;

class CookieScanResult extends Model
{
    protected $table = 'complihance_cookie_scan_results';

    protected $guarded = [];

    protected $casts = [
        'secure' => 'boolean',
        'http_only' => 'boolean',
        'expires_at' => 'datetime',
    ];
}
