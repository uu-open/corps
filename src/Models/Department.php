<?php

namespace UUPT\Corp\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Slowlyo\OwlAdmin\Models\BaseModel as Model;


class Department extends Model
{
    protected $fillable = ['name', 'parent_id', 'full_path','third_party_id','type'];

    use SoftDeletes;

    public function children()
    {
        return $this->hasMany(self::class, 'parent_id', 'id');
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }


    public function employees()
    {
        return $this->hasMany(Employee::class, 'department_id', 'id')->select(['name as label', 'id as value', 'department_id']);
    }

    public function name(): Attribute
    {
        return Attribute::get(function ($value) {
            $transKey = ($this->parent_id ? $this->parent_id . '::' : '') . "menu.{$value}";
            $translate = __($transKey);
            return $translate == $transKey ? $value : $translate;
        });
    }
}
