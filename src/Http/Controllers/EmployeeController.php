<?php

namespace UUPT\Corp\Http\Controllers;

use Slowlyo\OwlAdmin\Renderers\Page;
use Slowlyo\OwlAdmin\Renderers\Form;
use Slowlyo\OwlAdmin\Controllers\AdminController;
use Slowlyo\OwlAdmin\Renderers\TreeControl;
use Slowlyo\OwlAdmin\Renderers\Wrapper;
use UUPT\Corp\Services\DepartmentService;
use UUPT\Corp\Services\EmployeeService;

/**
 * 员工信息管理
 *
 * @property EmployeeService $service
 */
class EmployeeController extends AdminController
{
    protected string $serviceName = EmployeeService::class;

    public function list(): Page
    {
        return Page::make()->css(
            [
                '.cxd-Page-aside' => [
                    "min-width" => '250px !important;',
                    "border-right" => '0!important;'
                ]

            ])->aside(
            Wrapper::make()
                ->className('cxd-Crud')
                ->body(
                    TreeControl::make()
                        ->options(DepartmentService::deptTree())
                        ->name('department_tree')
                        ->inputClassName('no-border no-padder mt-1')
                        ->inputOnly(true)
                        ->selectFirst()
                        ->submitOnChange()->onEvent([
                            'change' => [
                                'weight' => '0',
                                'actions' => [
                                    [
                                        'componentId' => 'employee_list',
                                        'ignoreError' => '',
                                        'actionType' => 'reload',
                                        'data' => [
                                            'dept_id' => '${event.data.value}',
                                        ],
                                    ],
                                ],
                            ],
                        ])
                ))->body($this->baseCRUD()
            ->api($this->getListGetDataPath() . "&dept_id=\${dept_id}")
            ->filterTogglable(false)
            ->id('employee_list')
            ->headerToolbar([
                $this->createButton(true, 'lg'),
                ...$this->baseHeaderToolBar()
            ])
            ->columns([
                amis()->TableColumn('id', 'ID')->sortable(),
//                amis()->TableColumn('employee_id', '员工编号'),
                amis()->ImageControl('avatar', '头像')->type('avatar')->src('${avatar}'),
                amis()->TableColumn('name', '姓名'),
                amis()->TableColumn('gender', '性别')->type('map')->map(admin_dict()->getMapValues('sex')),
                amis()->TableColumn('email', '邮箱地址'),
                admin_corp_amis()->deptForm('department_ids', '所属部门')->static(),
                amis()->TableColumn('mobile', '手机号码'),
                amis()->TableColumn('position', '职位'),
                amis()->TableColumn('join_date', '入职日期'),
                amis()->TableColumn('created_at', __('admin.created_at'))->set('type', 'datetime')->sortable(),
                amis()->TableColumn('updated_at', __('admin.updated_at'))->set('type', 'datetime')->sortable(),
                $this->rowActions(true, 'lg')
            ]))->asideClassName('mr-3.5 border-r-none');
    }

    public function form($isEdit = false): Form
    {
        return $this->baseForm()->body([
            amis()->ImageControl('avatar', '头像'),
            amis()->TextControl('name', '姓名'),
            amis()->SwitchControl('state', '状态'),
            amis()->SelectControl('gender', '性别')->options(admin_dict()->getOptions('sex')),
            amis()->TextControl('email', '邮箱地址'),
            amis()->TextControl('mobile', '手机号码'),
            admin_corp_amis()->deptForm('department_ids', '所属部门'),
            amis()->TextControl('position', '职位'),
            amis()->DateTimeControl('join_date', '入职日期')->valueFormat('YYYY-MM-DD'),
//            amis()->TextControl('dingtalk_id', '钉钉ID'),
//            amis()->TextControl('lark_id', '飞书ID'),
//            amis()->TextControl('wechat_work_id', '企业微信ID'),
        ]);
    }

    public function detail(): Form
    {
        return $this->baseDetail()->body([
            amis()->GroupControl()->body(
                [
                    amis()->TextControl('id', 'ID')->static(),
                    amis()->ImageControl('avatar', '头像')->type('static-avatar')->src('${avatar}'),
                    amis()->StaticExactControl('name', '姓名'),
                ]
            ),
            amis()->GroupControl()->body(
                [
                    amis()->StaticExactControl('gender', '性别')->type('static-map')->map(admin_dict()->getMapValues('sex')),
                    amis()->StaticExactControl('email', '邮箱地址'),
                    amis()->StaticExactControl('mobile', '手机号码'),
                ]
            ),
            amis()->GroupControl()->body(
                [
                    admin_corp_amis()->deptForm('department_ids', '部门')->static(),
                    amis()->StaticExactControl('position', '职位'),
                    amis()->StaticExactControl('join_date', '入职日期'),
                ]
            ),
            amis()->GroupControl()->body(
                [
                    amis()->TextControl('created_at', __('admin.created_at'))->static(),
                    amis()->TextControl('updated_at', __('admin.updated_at'))->static()
                ]
            ),

//            amis()->StaticExactControl('dingtalk_id', '钉钉ID'),
//            amis()->StaticExactControl('lark_id', '飞书ID'),
//            amis()->StaticExactControl('wechat_work_id', '企业微信ID'),

        ]);
    }
}
