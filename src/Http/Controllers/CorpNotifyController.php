<?php

namespace UUPT\Corp\Http\Controllers;

use App\Jobs\TaskSyncUser;
use Illuminate\Http\Request;
use UUPT\Corp\CorpsServiceProvider;
use UUPT\Corp\Library\DingCrypt;
use UUPT\Corp\Services\DingService;

class CorpNotifyController
{
    private DingService $dingServices;

    public function __construct(DingService $dingServices)
    {
        $this->dingServices = $dingServices;
    }

    public function notify(Request $request)
    {
        if (CorpsServiceProvider::setting('dingtalk_enabled') == 0) {
            # 未开启
            return 'success';
        }
        $encrypt = $request->json('encrypt', '');
        $signature = $request->query('signature', '');
        $timeStamp = $request->query('timestamp');
        $nonce = $request->query('nonce');
        $client_id = CorpsServiceProvider::setting('client_id');
        $crypt = new DingCrypt(CorpsServiceProvider::setting('token'), CorpsServiceProvider::setting('aes_key'), $client_id);
        $encryData = $crypt->decryptMsg($signature, $timeStamp, $nonce, $encrypt);
//
        $file = storage_path('data/' . time() . '.json');
        file_put_contents($file, json_encode($encryData, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));

        switch ($encryData['EventType'] ?? '') {
            case 'check_url':
                break;
            case 'user_modify_org':
                foreach ($encryData['diffInfo'] as $diffInfo) {
                    TaskSyncUser::dispatch($diffInfo['userid']);
                }
                break;
            default:
                #保存到本地data/xxx.json 做为模拟数据

                break;
        }
        return $crypt->EncryptMsg('success', $client_id);
    }
}
