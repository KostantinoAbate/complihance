<?php

namespace KostantinoAbate\Complihance\Models;

use Illuminate\Database\Eloquent\Model;

class CookieScanResult extends Model
{
    protected $table = 'complihance_cookie_scan_results';

    protected $guarded = [];

    protected $fillable = [
        'identity_hash',
        'url',
        'name',
        'domain',
        'path',
        'secure',
        'http_only',
        'same_site',
        'expires_at',
    ];

    protected $casts = [
        'secure' => 'boolean',
        'http_only' => 'boolean',
        'expires_at' => 'datetime',
    ];
}
