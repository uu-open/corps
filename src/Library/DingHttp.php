<?php

namespace UUPT\Corp\Library;

use Exception;
use Illuminate\Support\Facades\Http;
use UUPT\Corp\CorpsServiceProvider;
use UUPT\Corp\Services\DingService;

/**
 * HTTP 封装类
 *
 * @method static mixed get(string $url, array $params = [], string $client_id = '', string $client_secret = '') 发送 GET 请求
 * @method static mixed post(string $url, array $params = [], string $client_id = '', string $client_secret = '') 发送 POST 请求
 * @method static mixed put(string $url, array $params = [], string $client_id = '', string $client_secret = '') 发送 POST 请求
 * @method static mixed patch(string $url, array $params = [], string $client_id = '', string $client_secret = '') 发送 POST 请求
 */
class DingHttp
{
    public static function __callStatic($method, $arguments)
    {
        $url = $arguments[0];
        $params = $arguments[1] ?? [];
        $client_id = $arguments[2] ?? '';
        $client_secret = $arguments[3] ?? '';
        // 检查方法是不是GET或POST，不是则抛出异常
        if (!in_array(strtolower($method), ['get', 'post', 'put', 'delete', 'patch'])) {
            throw new Exception("Unsupported method {$method}");
        }
        // 兼容v2判断域名是钉钉v2的，把access_token放在头部
        if (str_contains($url, 'api.dingtalk.com/v1.0')) {
            $headers = [
                'Content-Type' => 'application/json',
                'x-acs-dingtalk-access-token' => self::getAccessToken($client_id, $client_secret)
            ];
            $response = Http::withHeaders($headers)->$method($url, $params);
        } else {
            $params['access_token'] = self::getAccessToken($client_id, $client_secret);
            $response = Http::$method($url, $params);
        }

        return $response->json();
    }


    private static function getAccessToken($client_id = '', $client_secret = '')
    {
        $client_id = $client_id ?: CorpsServiceProvider::setting('client_id');
        $client_secret = $client_secret ?: CorpsServiceProvider::setting('client_secret');
        return DingService::getAccessToken($client_id, $client_secret);
    }
}
