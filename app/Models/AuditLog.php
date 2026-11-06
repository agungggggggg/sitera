<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['actor_id', 'action', 'result', 'subject_type', 'subject_id', 'metadata'])]
class AuditLog extends Model
{
    public const ACTIONS = ['user.created', 'user.status_changed', 'user.deleted', 'user.pic_verified', 'user.password_reset', 'cpcl.created', 'cpcl.import_previewed', 'cpcl.import_confirmed', 'cpcl.import_cancelled', 'auth.login', 'auth.logout', 'report.submitted', 'report.verified'];

    public const RESULTS = ['success', 'failure', 'denied'];

    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_id')->withTrashed();
    }

    protected function casts(): array
    {
        return ['metadata' => 'array'];
    }
}
