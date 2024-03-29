<?php

namespace UUPT\Corp\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Slowlyo\OwlAdmin\Models\BaseModel as Model;

/**
 * 员工信息管理
 */
class Employee extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'dingtalk_id',
        'name',
        'mobile',
        'email',
        'position',
        'department_ids',
        'department_id',
        'avatar',
        'join_date',
    ];

    public function avatar(): \Illuminate\Database\Eloquent\Casts\Attribute
    {
        return file_upload_handle();
    }
}
