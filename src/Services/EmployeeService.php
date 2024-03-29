<?php

namespace UUPT\Corp\Services;

use Illuminate\Database\Query\Builder;
use Slowlyo\OwlAdmin\Services\AdminService;
use UUPT\Corp\Models\Department;
use UUPT\Corp\Models\Employee;

/**
 * 员工信息管理
 *
 * @method Employee getModel()
 * @method Employee|\Illuminate\Database\Query\Builder query()
 */
class EmployeeService extends AdminService
{
    protected string $modelName = Employee::class;


    public function saving(&$data, $primaryKey = '')
    {
        $department_ids = explode(',', $data['department_ids'] ?? []);
        $data['department_id'] = current($department_ids);
    }


    public static function employeeTree(string $string)
    {
        return Employee::query()->select(['name as label','id as value','department_id'])->get()->toArray();
    }

    /**
     * 搜索
     *
     * @return void
     * @var Builder $query
     *
     */
    public function searchable($query)
    {
        $data = request()->query();
        $query->when($data['dept_id'], function ($query) use ($data) {
            #数据库中位字符串储存，结构为dept_id 为  1,2,3
            $dept_ids = explode(',', $data['dept_id']);
            # 获取这些部门ID下的所有部门id
            Department::query()->whereIn('id', $dept_ids)->with('children')->get()->each(function ($dept) use (&$dept_ids) {
                $dept_ids = array_merge($dept_ids, $dept->children->pluck('id')->toArray());
            });
            $dept_ids = array_unique($dept_ids);
            foreach ($dept_ids as $id) {
                $query->orWhereRaw("find_in_set(?,department_ids)", [$id]);
            }
        });
    }
}
