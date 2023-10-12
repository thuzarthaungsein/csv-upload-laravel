<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class CsvFile extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'created_at'      => 'datetime',
        'updated_at'     => 'datetime',
    ];

    public function progress(): HasOne
    {
        return $this->hasOne(Progress::class);
    }
}
