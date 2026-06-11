<?php

namespace App\Observers;

use App\Traits\LogsAudit;

class AuditObserver
{
    use LogsAudit;

    protected string $module;

    public function created($model): void
    {
        self::logAction(
            action: 'create',
            module: $this->module,
            description: "Created {$this->module} record ID {$model->id}",
            newValues: $model->getAttributes(),
        );
    }

    public function updated($model): void
    {
        $dirty = $model->getDirty();
        $original = array_intersect_key($model->getOriginal(), $dirty);

        self::logAction(
            action: 'update',
            module: $this->module,
            description: "Updated {$this->module} record ID {$model->id}",
            oldValues: $original,
            newValues: $dirty,
        );
    }

    public function deleted($model): void
    {
        self::logAction(
            action: 'delete',
            module: $this->module,
            description: "Deleted {$this->module} record ID {$model->id}",
            oldValues: $model->getAttributes(),
        );
    }
}   