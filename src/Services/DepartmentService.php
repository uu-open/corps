<?php

namespace UUPT\Corp\Services;

use Slowlyo\OwlAdmin\Services\AdminService;
use UUPT\Corp\Models\Department;

/**
 * 部门管理
 *
 * @method Department getModel()
 * @method Department|\Illuminate\Database\Query\Builder query()
 */
class DepartmentService extends AdminService
{
    protected string $modelName = Department::class;

    /**
     * 搜索
     *
     * @param $query
     *
     * @return void
     */
    public function searchable($query)
    {
        $params = request()->query();
        $department_id = $params['id'] ?? 0;
        $query->when($department_id > 0, function ($query) use ($department_id) {
            $query->where('parent_id', $department_id);
        });
    }


    public function saving(&$data, $primaryKey = '')
    {
        if (!isset($data['code'])) {
            $data['code'] = uniqid('dept_');
        }

        $data['type'] = 1;

    }
    public static function deptTree($rootName = '全部部门')
    {
        return [
            [
                'label' => $rootName,
                'value' => 0,
                'children' => Department::with('children')->where('parent_id', 0)->get()->map(function ($dept) {
                    // 构建每个顶级项的基础结构
                    $item = [
                        'label' => $dept->name, // 假设有一个name字段代表页面或部门名称
                        'value' => $dept->id,   // 使用id作为value
                    ];
                    // 如果有子部门，处理子部门
                    if ($dept->children->isNotEmpty()) {
                        $item['children'] = $dept->children->map(function ($child) use ($dept) {
                            // 基础结构
                            $childItem = [
                                'label' => $child->name,
                                'value' => $child->id,
                            ];
                            // 递归处理更深层次的子部门
                            if ($child->children->isNotEmpty()) {
                                $childItem['children'] = $child->children->map(function ($subChild) {
                                    return [
                                        'label' => $subChild->name,
                                        'value' => $subChild->id,
                                    ];
                                })->toArray();
                            }

                            return $childItem;
                        })->toArray();
                    }
                    return $item;
                })
            ]
        ];
    }

}
