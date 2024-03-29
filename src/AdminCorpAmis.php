<?php

namespace UUPT\Corp;

use Illuminate\Support\Arr;
use Slowlyo\OwlAdmin\Renderers\Page;
use Slowlyo\OwlAdmin\Renderers\TreeControl;
use Slowlyo\OwlAdmin\Renderers\Wrapper;
use Slowlyo\OwlDict\Services\AdminDictService as Service;
use UUPT\Corp\Services\DepartmentService;
use UUPT\Corp\Services\EmployeeService;

class AdminCorpAmis
{
    private $data;


    public function deptForm($name = 'dept_id', $label = '部门', $select_dept = true, $select_user = false)
    {
        $amis = amis()->TabsTransferPickerControl($name, $label)
            ->valueTpl('${label}');
        $options = [];
        if ($select_dept) {
            $options[] = [
                'label' => '部门',
                'selectMode' => 'tree',
                'searchable' => true,
                'children' => DepartmentService::deptTree("顶级部门")
            ];
        }
        if ($select_user) {
            $options[] = [
                'label' => '成员',
                'selectMode' => 'tree',
                'searchable' => true,
                'multiple' => false,
                'children' => EmployeeService::employeeTree("顶级部门")
            ];
        }
        return $amis->options($options)->set('pickerSize', 'md');
    }

    public function employeeForm($name = 'dept_id', $label = '员工')
    {

        $amis = amis()->TabsTransferPickerControl($name, $label)
            ->multiple(false)
            ->valueTpl('${label}');
        $options = [];
        $options[] = [
            'label' => '成员',
            'selectMode' => 'tree',
            'searchable' => true,
            'multiple' => false,
            'children' => EmployeeService::employeeTree("顶级部门")
        ];
        return $amis->options($options)->set('pickerSize', 'md');
    }

    private function baseCRUD()
    {
        $crud = amis()->CRUDTable()
            ->perPage(100)
            ->affixHeader(false)
            ->filterTogglable()
            ->filterDefaultVisible(false)
            ->api(admin_url('employees?id=\${id}'))
            ->perPageAvailable([10, 20, 30, 50, 100, 200])
            ->set('primaryField', 'id');
        return $crud;
    }
}
