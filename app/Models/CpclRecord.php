<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CpclRecord extends Model
{
    protected $fillable = [
        'no', 'nama_di', 'kewenangan', 'keterangan', 'provinsi', 'kabupaten',
        'kecamatan', 'desa', 'kel_tani', 'nama_ketua', 'realisasi', 'luas_ha',
        'x', 'y', 'aktif', 'created_by',
    ];

    protected function casts(): array
    {
        return ['aktif' => 'boolean', 'luas_ha' => 'decimal:2', 'x' => 'decimal:7', 'y' => 'decimal:7'];
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by')->withTrashed();
    }

    public function reports(): HasMany
    {
        return $this->hasMany(Report::class, 'cpcl_id');
    }
}
