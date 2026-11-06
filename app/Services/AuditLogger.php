<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class AuditLogger
{
    /**
     * Only pass server-owned identifiers and outcome enums, never request data.
     * Free-text messages, credentials, headers, cookies, IPs and request dumps are deliberately unsupported.
     *
     * @param  array<string, mixed>  $metadata
     */
    public function log(?User $actor, string $action, string $result, ?string $subjectType = null, int|string|null $subjectId = null, array $metadata = []): AuditLog
    {
        Validator::make([
            'action' => $action, 'result' => $result, 'subject_type' => $subjectType, 'subject_id' => $subjectId,
        ], [
            'action' => ['required', Rule::in(AuditLog::ACTIONS)],
            'result' => ['required', Rule::in(AuditLog::RESULTS)],
            'subject_type' => ['nullable', Rule::in(['user', 'cpcl', 'import_batch', 'report'])],
            'subject_id' => ['nullable', 'regex:/^[0-9a-fA-F-]{1,80}$/'],
        ])->validate();

        $safe = [];
        foreach (['count', 'created_count', 'skipped_count', 'failed_count'] as $key) {
            if (isset($metadata[$key]) && is_int($metadata[$key]) && $metadata[$key] >= 0) {
                $safe[$key] = $metadata[$key];
            }
        }
        if (isset($metadata['is_active']) && is_bool($metadata['is_active'])) {
            $safe['is_active'] = $metadata['is_active'];
        }

        return AuditLog::query()->create([
            'actor_id' => $actor?->id, 'action' => $action, 'result' => $result,
            'subject_type' => $subjectType, 'subject_id' => $subjectId === null ? null : (string) $subjectId,
            'metadata' => $safe,
        ]);
    }
}
