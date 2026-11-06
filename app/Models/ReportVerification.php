<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['report_id', 'verifier_id', 'hasil', 'catatan', 'diperiksa_pada'])]
class ReportVerification extends Model
{
    protected function casts(): array
    {
        return ['diperiksa_pada' => 'datetime'];
    }

    public function report(): BelongsTo
    {
        return $this->belongsTo(Report::class);
    }

    public function verifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verifier_id')->withTrashed();
    }
}
