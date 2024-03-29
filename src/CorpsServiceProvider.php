<?php

namespace UUPT\Corp;

use Slowlyo\OwlAdmin\Renderers\TextControl;
use Slowlyo\OwlAdmin\Extend\ServiceProvider;
use Slowlyo\OwlDict\AdminDict;
use Slowlyo\OwlDict\Models\AdminDict as AdminDictModel;

class CorpsServiceProvider extends ServiceProvider
{
    protected $exceptRoutes = [
        'auth' => [
            'corp/notify'
        ]
    ];

    protected $menu = [
        [
            'parent' => 0,
            'title' => '组织管理',
            'url' => '/corps',
            'url_type' => '1',
            'keep_alive' => '1',
            'icon' => 'clarity:employee-line',
        ],
        [
            'parent' => '组织管理', // 此处父级菜单根据 title 查找
            'title' => '部门管理',
            'url' => '/departments',
            'url_type' => '1',
            'icon' => 'material-symbols-light:corporate-fare',
        ],
        [
            'parent' => '组织管理', // 此处父级菜单根据 title 查找
            'title' => '员工管理',
            'url' => '/employees',
            'url_type' => '1',
            'icon' => 'clarity:employee-group-line',
        ],
    ];

    /**
     * 安装
     *
     * @return void
     * @throws \Exception
     */
    public function install()
    {
        # 注册字典数据
        $this->registerDict();


        $this->publishable();
        $this->runMigrations();
    }

    public function register()
    {
        parent::register();

        $this->app->singleton('admin.corp.amis', AdminCorpAmis::class);
    }

    public function settingForm()
    {
        return $this->baseSettingForm()->body([
            amis()->Tabs()->tabs([
                [
                    'title' => '钉钉配置',
                    'body' => [
                        amis()->Divider()->title('基础配置'),
                        amis()->TextControl()->label('CorpID')->name('corp_id'),
                        amis()->TextControl()->name('client_id')->label('ClientId'),
                        amis()->TextControl()->name('client_secret')->label('Client Secret'),
                        amis()->TextControl()->name('agent_id')->label('AgentID'),
                        amis()->Divider()->title('异步推送配置'),
                        amis()->TextControl()->name('aes_key')->label('加密aes_key'),
                        amis()->TextControl()->name('token')->label('签名 token'),
                        amis()->StaticExactControl()->label('请求网址')->value(url()->to(admin_url('/corp/notify', true)))->copyable([
                            'content' => url()->to(admin_url('/corp/notify', true)),
                        ])->desc('请将此网址填入钉钉回调地址'),
                        amis()->RadiosControl('dingtalk_enabled', '是否启用')->options([
                            ['label' => '启用', 'value' => 1],
                            ['label' => '禁用', 'value' => 0],
                        ])->value(1),
                        amis()->StaticExactControl()->label('启动命令')->value('php artisan queue:work --daemon --sleep=3 --tries=3')->copyable([
                            'content' => 'php artisan queue:work --daemon --sleep=3 --tries=3',
                        ])->desc('同步事件为异步执行，需要服务器开启queue worker进程, 请将此命令填入服务器添加到守护进程执行'),
                    ]
                ],
                [
                    'title' => '飞书配置',
                    'body' => '暂未开放',
                    'id' => 'u:e7e0a969e59f',
                ],
            ])->id('u:c934793fb1b4'),
        ]);
    }

    private function registerDict()
    {
        $dicts = [
            [
                'key' => 'dept_srouce',
                'value' => '部门来源',
                'keys' => [
                    ['key' => 1, 'value' => '自建'],
                    ['key' => 2, 'value' => '钉钉'],
                    ['key' => 3, 'value' => '飞书'],
                    ['key' => 4, 'value' => '企业微信'],
                ]
            ],
            [
                'key' => 'sex',
                'value' => '性别',
                'keys' => [
                    ['key' => "1", 'value' => '男'],
                    ['key' => "2", 'value' => '女'],
                    ['key' => "0", 'value' => '保密'],
                ]
            ]
        ];
        foreach ($dicts as $dict) {
            $dictModel = AdminDictModel::query()->where('key', $dict['key'])->first();
            if (!$dictModel) {
                $dictModel = new AdminDictModel();
                $dictModel->value = $dict['value'];
                $dictModel->enabled = 1;
                $dictModel->key = $dict['key'];
                $dictModel->save();
            }
            foreach ($dict['keys'] as $value) {
                $dictValueModel = AdminDictModel::query()->where('parent_id', $dictModel->id)->where('key', $value['key'])->first();
                if (!$dictValueModel) {
                    $dictValueModel = new AdminDictModel();
                    $dictValueModel->parent_id = $dictModel->id;
                    $dictValueModel->key = $value['key'];
                    $dictValueModel->value = $value['value'];
                    $dictValueModel->enabled = 1;
                    $dictValueModel->save();
                }
            }
        }
    }
}
