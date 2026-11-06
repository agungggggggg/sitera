<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Visible;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

#[Fillable([
    'user_id',
    'cpcl_id',
    'versi',
    'koreksi_dari',
    'nomor_tiket',
    'status',
    'tanggal_kejadian',
    'provinsi',
    'kabupaten',
    'kecamatan',
    'desa',
    'nama_saluran',
    'jenis_kerusakan',
    'deskripsi',
    'panjang_terdampak_m',
    'luas_terdampak_ha',
    'koordinat',
    'catatan',
    'sumber_isian',
    'dikirim_pada',
])]
#[Visible(['nomor_tiket', 'status', 'tanggal_kejadian', 'provinsi', 'kabupaten', 'jenis_kerusakan'])]
class Report extends Model
{
    protected function casts(): array
    {
        return [
            'tanggal_kejadian' => 'date',
            'panjang_terdampak_m' => 'decimal:2',
            'luas_terdampak_ha' => 'decimal:2',
            'dikirim_pada' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class)->withTrashed();
    }

    public function cpclRecord(): BelongsTo
    {
        return $this->belongsTo(CpclRecord::class, 'cpcl_id');
    }

    public function verification(): HasOne
    {
        return $this->hasOne(ReportVerification::class);
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(ReportAttachment::class);
    }

    public function originalReport(): BelongsTo
    {
        return $this->belongsTo(self::class, 'koreksi_dari');
    }

    public function correction(): HasOne
    {
        return $this->hasOne(self::class, 'koreksi_dari');
    }
}
