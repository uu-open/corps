<?php

namespace UUPT\Corp\Services;

use DateTime;
use Illuminate\Database\Eloquent\Model;
use UUPT\Corp\Models\Department;
use UUPT\Corp\Models\Employee;

abstract class SyncService implements SyncServiceInterface
{
    /**
     * 同步部门，通用方法
     * @param array $array
     * @return \Illuminate\Database\Eloquent\Builder|Model
     */
    protected function syncUpdateDepartment(array $array)
    {
        return Department::query()->where('third_party_id', $array['third_party_id'])->updateOrCreate([
            'third_party_id' => $array['third_party_id'],
        ], [
            'name' => $array['name'],
            'third_party_id' => $array['third_party_id'],
            'parent_id' => cache()->remember('ding_dept_id:' . $array['parent_id'], 3600 * 24 * 365, function () use ($array) {
                return Department::query()->where('third_party_id', $array['parent_id'])->value('id');
            }),
            'type' => $array['type'],
        ]);
    }


    /**
     * 同步部门，通用方法
     * @param array $user
     * @return void
     * @throws \Exception
     */
    protected function syncUpdateUser(array $user)
    {
        # 钉钉的需要特殊处理
        if (isset($user['orderInDepts'])) {
            $order_depts = preg_replace('/(\d+):(\d+)/', '"$1":"$2"', $user['orderInDepts']);
            $order_depts = json_decode($order_depts, true);
        }
        if (isset($user['gmtCreate'])) {
            $dateTime = new DateTime($user['gmtCreate']);
            $user['join_date'] = $dateTime->format('Y-m-d H:i:s');
        }
        $user = array_filter($user);
        switch (true) {
            # 钉钉的同步用户  后面按需求在添加飞书、企业微信等
            case isset($user['dingtalk_id']):
                # 钉钉的部门也需要特殊处理
                $dept_ids = [];
                foreach ($order_depts as $deptId => $order) {
                    $dept_ids[] = cache()->remember('ding_dept_id:' . $deptId, 3600 * 24 * 365, function () use ($deptId) {
                        if ($deptId == 1) {
                            return 0;
                        }
                        return Department::query()->where('third_party_id', $deptId)->value('id');
                    });
                }
                $user['department_ids'] = implode(',', $dept_ids);
                $user['department_id'] = current($dept_ids);
                Employee::query()->updateOrCreate([
                    'dingtalk_id' => $user['dingtalk_id']
                ], $user);
                break;
        }
    }


    public static function make($type = 'dingtak')
    {
        switch ($type) {
            case 'dingtalk':
                return new DingService();
            default:
                throw new \Exception("暂时仅支持钉钉");
                break;
        }
    }
}
