<?php

namespace App\Traits;

use App\Services\SystemLogger;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Throwable;

/**
 * Idagdag sa kahit anong Eloquent model para ma-log ang create / update / delete.
 *
 *   class InventoryItem extends Model
 *   {
 *       use LogsActivity;
 *       protected string $logModule = 'inventory';   // optional, kung wala: pangalan ng class
 *       protected string $logLabel  = 'name';        // optional, field na ipapakita sa description
 *   }
 *
 * Tandaan: ang mass update/delete gamit ang query builder (Model::where(...)->delete())
 * ay HINDI nagti-trigger ng model events, kaya hindi ito mala-log ng trait.
 */
trait LogsActivity
{
    // Mga method na galing sa Eloquent Model. Idineklara dito para
    // makilala ng Intelephense; ang Model mismo ang nag-i-implement.
    abstract public function getAttribute(mixed $key): mixed;

    abstract public function getKey(): mixed;

    abstract public function getChanges(): array;

    abstract public function getOriginal(mixed $key = null, mixed $default = null): mixed;

    abstract protected static function registerModelEvent(string $event, mixed $callback): void;

    public static function bootLogsActivity(): void
    {
        static::registerModelEvent('created', fn ($model) => $model->writeActivityLog('created'));
        static::registerModelEvent('updated', fn ($model) => $model->writeActivityLog('updated'));
        static::registerModelEvent('deleted', fn ($model) => $model->writeActivityLog('deleted'));
    }

    protected function activityModule(): string
    {
        return property_exists($this, 'logModule') ? $this->logModule : Str::snake(class_basename($this));
    }

    protected function activityLabel(): string
    {
        $field = property_exists($this, 'logLabel') ? $this->logLabel : null;
        $label = $field ? $this->getAttribute($field) : null;

        return $label ? "#{$this->getKey()} ({$label})" : "#{$this->getKey()}";
    }

    protected function writeActivityLog(string $action): void
    {
        // Huwag mag-log habang seeding / migration / artisan commands
        if (app()->runningInConsole() && ! app()->runningUnitTests()) {
            return;
        }

        try {
            $module = $this->activityModule();
            $label = $this->activityLabel();
            $hidden = (array) config('system_log.hidden_fields', []);
            $context = [];

            if ($action === 'updated') {
                $changes = [];
                foreach ($this->getChanges() as $field => $new) {
                    if (in_array($field, $hidden, true)) {
                        continue;
                    }
                    $old = $this->getOriginal($field);
                    $changes[$field] = [
                        'from' => is_string($old) ? Str::limit($old, 100) : $old,
                        'to' => is_string($new) ? Str::limit($new, 100) : $new,
                    ];
                }

                if (! $changes) {
                    return; // walang makabuluhang nagbago
                }
                $context['changes'] = $changes;
            }

            $verb = ['created' => 'Created', 'updated' => 'Updated', 'deleted' => 'Deleted'][$action];
            $severity = $action === 'deleted' ? 'warning' : 'info';

            SystemLogger::record(
                "{$module}_{$action}",
                "{$verb} {$module} {$label}.",
                $severity,
                $context,
                false,
                null,
                null,
                $module
            );

            if ($action === 'deleted') {
                SystemLogger::checkBulkDelete(Auth::id(), $module);
            }
        } catch (Throwable $e) {
            // Hindi dapat masira ang app dahil lang sa logging
            report($e);
        }
    }
}