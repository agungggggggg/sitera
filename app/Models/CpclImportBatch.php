<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CpclImportBatch extends Model
{
    protected $fillable = ['user_id', 'source_name', 'rows', 'summary', 'confirmed_at'];

    protected function casts(): array
    {
        return ['rows' => 'array', 'summary' => 'array', 'confirmed_at' => 'datetime'];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
