<?php

namespace App\Traits;

use App\Models\Action;

trait HasActions
{
    public static function bootHasActions()
    {
        static::created(function ($model) {
            $model->recordAction('created', [
                'new' => $model->getAttributes(), // yangi yozilgan barcha fieldlar
            ]);
        });
        static::updated(fn($model) => $model->recordAction('updated', [
            'old' => $model->getOriginal(),
            'new' => $model->getDirty(),
        ]));
        static::deleted(fn($model) => $model->recordAction('deleted'));
    }

    public function recordAction($action, $changes = null)
    {
        $actor = auth('employee')->check()
            ? auth('employee')->user()
            : auth('web')->user();

        Action::create([
            'actor_type' => $actor ? get_class($actor) : null,
            'actor_id' => $actor?->id,
            'action' => $action,
            'actionable_type' => get_class($this),
            'actionable_id' => $this->id,
            'changes' => $changes,
        ]);
    }

    public function actions()
    {
        return $this->morphMany(Action::class, 'actionable');
    }
}
