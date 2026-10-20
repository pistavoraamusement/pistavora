<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * System / activity log.
 *
 * SystemLog::record('booking.created', 'Created booking #12', null, 'booking', 12);
 *
 * Never throws: a logging problem must not break the user's request.
 * Never store passwords, codes, tokens or request bodies here.
 */
class SystemLog extends Model
{
    public $timestamps = false;

    protected $table = 'system_logs';

    protected $fillable = [
        'event_type', 'severity', 'module', 'user_id', 'email',
        'ip_address', 'user_agent', 'method', 'url',
        'description', 'context', 'is_anomaly', 'created_at',
        'review_status', 'reviewed_by', 'reviewed_at',
    ];

    protected $casts = [
        'context'     => 'array',
        'is_anomaly'  => 'boolean',
        'created_at'  => 'datetime',
        'reviewed_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by', 'user_id');
    }

    public static function record(
        string $action,
        string $description,
        ?int $userId = null,
        ?string $subjectType = null,
        ?int $subjectId = null,
        ?string $role = null
    ): void {
        try {
            $request     = request();
            $sessionUser = session('user_id');

            // "auth.login_failed" -> module "auth"
            $module   = preg_split('/[._]/', $action)[0];
            $severity = str_contains($action, 'failed') ? 'warning' : 'info';

            $context = array_filter([
                'role'         => $role ?? session('role'),
                'subject_type' => $subjectType,
                'subject_id'   => $subjectId,
            ], fn ($v) => $v !== null);

            DB::table('system_logs')->insert([
                'event_type'  => mb_substr($action, 0, 60),
                'severity'    => $severity,
                'module'      => $module,
                'user_id'     => $userId ?? ($sessionUser !== null ? (int) $sessionUser : null),
                'email'       => session('email'),
                'ip_address'  => $request ? $request->ip() : null,
                'user_agent'  => $request ? mb_substr((string) $request->userAgent(), 0, 255) : null,
                'method'      => $request ? $request->method() : null,
                'url'         => $request ? mb_substr($request->fullUrl(), 0, 255) : null,
                'description' => mb_substr($description, 0, 255),
                'context'     => $context ? json_encode($context) : null,
                'is_anomaly'  => 0,
                'created_at'  => now(),
            ]);
        } catch (\Throwable $e) {
            Log::warning('System log write failed: ' . $e->getMessage());
        }
    }
}