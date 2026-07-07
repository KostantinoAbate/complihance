<?php

namespace KostantinoAbate\Complihance\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

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

    public function results(): HasMany
    {
        return $this->hasMany(CookieScanResult::class, 'scan_id');
    }
}
