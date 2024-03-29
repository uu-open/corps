<?php

namespace UUPT\Corp\Services;

use App\Jobs\TaskSyncDepartment;
use App\Jobs\TaskSyncUser;
use Illuminate\Support\Facades\Http;
use UUPT\Corp\CorpsServiceProvider;

class DingService extends SyncService
{
    // 同步部门
    public function syncDepartment($dept_id = 1) // 默认从根部门开始同步
    {
        $departments = $this->get('https://oapi.dingtalk.com/department/list', [
            'id' => $dept_id
        ]);
        foreach ($departments['department'] as $dept) {
            // 这里以简化的方式处理，实际项目中可能需要更复杂的逻辑来同步或更新本地数据库的部门信息
            // 假设有一个syncUpdateDepartment方法用于处理部门数据的同步/更新
            $this->syncUpdateDepartment([
                'third_party_id' => $dept['id'],
                'type' => 2,
                'name' => $dept['name'],
                'parent_id' => $dept['parentid'] ?? 0,
            ]);
            // 递归同步子部门
            TaskSyncDepartment::dispatch($dept['id']);
            $department_userList = $this->get('https://oapi.dingtalk.com/topapi/user/listid', [
                'dept_id' => $dept['id']
            ]);

            foreach ($department_userList['result']['userid_list'] as $userId) {
                TaskSyncUser::dispatch($userId);
            }
        }
    }

    // 同步用户
    public function syncUser($dept_id = 1) // 默认从根部门开始同步用户
    {
        $users = $this->get('https://oapi.dingtalk.com/user/simplelist', [
            'department_id' => 1
        ]);

        foreach ($users['userlist'] as $user) {
            // 获取更详细的用户信息
            TaskSyncUser::dispatch($user['userid']);
        }
    }

    public function getUserInfo($userid = null)
    {
        $detailedUser = $this->get('https://oapi.dingtalk.com/user/get', [
            'userid' => $userid
        ]);
        // 添加dingtalk_id到用户信息中
        $detailedUser['dingtalk_id'] = $detailedUser['userid'];
        // 同步或更新用户信息
        $this->syncUpdateUser($detailedUser);

    }


    # 封装get 请求，自动携带accessToken

    public function get($url, $params = [])
    {
        $params['access_token'] = $this->getAccessToken(CorpsServiceProvider::setting('client_id'), CorpsServiceProvider::setting('client_secret'));
        return Http::get($url, $params)->json();
    }

    private function getAccessToken($client_id, $client_secret)
    {
        # 请求钉钉api接口，获取token 并且缓存7200 秒
        return cache()->remember($client_id . ':ding_access_token', 7200, function () use ($client_id, $client_secret) {
            $response = Http::get('https://oapi.dingtalk.com/gettoken', [
                'appkey' => $client_id,
                'appsecret' => $client_secret
            ]);
            return $response->json('access_token');
        });
    }
}
