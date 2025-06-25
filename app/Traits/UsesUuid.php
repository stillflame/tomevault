<?php

// app/Traits/UsesUuid.php
namespace App\Traits;

use Illuminate\Support\Str;

trait UsesUuid
{
    protected static function bootUsesUuid(): void
    {
        static::creating(static function ($model) {
            if (! $model->getKey()) {
                $model->{$model->getKeyName()} = (string) Str::uuid();
            }
        });
    }

    public function getIncrementing(): bool
    {
        return false;
    }

    public function getKeyType(): string
    {
        return 'string';
    }
}
