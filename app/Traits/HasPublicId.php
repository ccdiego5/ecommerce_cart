<?php

namespace App\Traits;

use Illuminate\Support\Facades\DB;

trait HasPublicId
{
    /**
     * Boot function from Laravel.
     */
    protected static function bootHasPublicId(): void
    {
        static::creating(function ($model) {
            if (empty($model->public_id)) {
                $sequenceName = $model->getTable() . '_public_id_seq';
                $nextVal = DB::selectOne("SELECT nextval('{$sequenceName}') as val");
                $model->public_id = $nextVal->val;
            }
        });
    }

    /**
     * Get the formatted public ID with leading zeros (0001, 0002, etc).
     */
    public function getFormattedPublicIdAttribute(): string
    {
        return str_pad($this->public_id ?? 0, 4, '0', STR_PAD_LEFT);
    }
}

