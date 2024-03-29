<?php

namespace UUPT\Corp\Http\Controllers;

use Slowlyo\OwlAdmin\Admin;
use Slowlyo\OwlAdmin\Controllers\AdminController;
use Slowlyo\OwlAdmin\Renderers\Form;
use Slowlyo\OwlAdmin\Renderers\Page;
use Slowlyo\OwlAdmin\Renderers\TreeControl;
use Slowlyo\OwlAdmin\Renderers\Wrapper;
use UUPT\Corp\CorpsServiceProvider;
use UUPT\Corp\Services\DepartmentService;
use UUPT\Corp\Services\DingService;

/**
 * 部门管理
 *
 * @property DepartmentService $service
 */
class DepartmentController extends AdminController
{
    protected string $serviceName = DepartmentService::class;

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
                        ->options($this->service::deptTree())
                        ->name('department_tree')
                        ->inputClassName('no-border no-padder mt-1')
                        ->inputOnly(true)
                        ->selectFirst()
                        ->submitOnChange()->onEvent([
                            'change' => [
                                'weight' => '0',
                                'actions' => [
                                    [
                                        'componentId' => 'u:f9a819bafd1b',
                                        'ignoreError' => '',
                                        'actionType' => 'reload',
                                        'data' => [
                                            'id' => '${event.data.value}',
                                        ],
                                    ],
                                ],
                            ],
                        ])
                ))->body($this->baseCRUD()
            ->api($this->getListGetDataPath() . "&id=\${id}")
            ->filterTogglable(false)
            ->id('u:f9a819bafd1b')
            ->headerToolbar([
                $this->createButton(true, 'lg'),
                $this->syncButton(),
                ...$this->baseHeaderToolBar()
            ])
            ->columns([
                amis()->TableColumn('id', 'ID')->sortable(),
                amis()->TableColumn('name', '部门名称'),
                amis()->TableColumn('code', '部门代码'),
                amis()->TableColumn('parent_id', '上级部门ID')->sortable(),
                admin_corp_amis()->employeeForm('leader_user_id', '部门负责人')->static(),
                amis()->TableColumn('type', '部门类型')->type('map')->map(admin_dict()->getMapValues('dept_srouce')),
                amis()->TableColumn('order', '排序')->sortable(),
                amis()->TableColumn('state', '状态0 禁用 1启用'),
                amis()->TableColumn('created_at', __('admin.created_at'))->set('type', 'datetime')->sortable(),
                amis()->TableColumn('updated_at', __('admin.updated_at'))->set('type', 'datetime')->sortable(),
                $this->rowActions(true, 'lg')
            ]))->asideClassName('mr-3.5 border-r-none');
    }

    public function form($isEdit = false): Form
    {
        return $this->baseForm()->body([
            amis()->TextControl('name', '部门名称'),
//            amis()->TextControl('code', '部门代码'),
            amis()->GroupControl()->body([
                amis()->TreeSelectControl('department_ids', '部门')->options(DepartmentService::deptTree('顶级部门')),
            ]),

            admin_corp_amis()->employeeForm('leader_user_id', '部门负责人'),
            amis()->TextControl('third_party_id', '三方对应部门ID'),
            amis()->TextControl('order', '排序'),
            amis()->SwitchControl('state', '状态'),
        ]);
    }

    public function detail(): Form
    {
        return $this->baseDetail()->body([

            amis()->GroupControl()->body(
                [
                    amis()->TextControl('id', 'ID')->static(),
                    amis()->StaticExactControl('name', '部门名称'),
                ]
            ),

            amis()->GroupControl()->body(
                [
                    amis()->StaticExactControl('code', '部门代码'),
                    amis()->StaticExactControl('parent_id', '上级部门ID'),
                ]
            ),

            amis()->GroupControl()->body(
                [
                    amis()->StaticExactControl('third_party_id', '三方对应部门ID'),
                    admin_corp_amis()->employeeForm('leader_user_id', '部门负责人')->static(),
                ]
            ),

            amis()->GroupControl()->body(
                [
                    amis()->TextControl('order', '排序')->static(),
                    amis()->TextControl('state', '状态0 禁用 1启用')->static(),
                ]
            ),
            amis()->TextControl('created_at', __('admin.created_at'))->static(),
            amis()->TextControl('updated_at', __('admin.updated_at'))->static()
        ]);
    }

    public function sync()
    {
        if (CorpsServiceProvider::setting('dingtalk_enabled') == 0) {
            return Admin::response()->fail("钉钉同步未开启");
        }
        app(DingService::class)->syncDepartment();
        return Admin::response()->successMessage("同步成功，系统将在后台自动同步，预计1-10分钟内，如果人数较多等待时间会比较长！");
    }

    private function syncButton()
    {
        $btns = [];
        if (CorpsServiceProvider::setting('dingtalk_enabled') == 1) {
            $btns[] = amis()->AjaxAction()
                ->label("同步组织架构")
                ->level('success')
                ->confirmText("同步后系统将在后台自动同步，预计1-10分钟内，如果人数较多等待时间会比较长！")
                ->api($this->queryPath . "/sync");
        }
        return $btns;
    }
}
