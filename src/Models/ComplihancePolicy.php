<?php

namespace KostantinoAbate\Complihance\Models;

use Illuminate\Database\Eloquent\Model;

class ComplihancePolicy extends Model
{
    protected $table = 'complihance_policies';

    protected $guarded = [];

    protected $casts = [
        'is_active' => 'boolean',
        'published_at' => 'datetime',
    ];
}
