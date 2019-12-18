<?php

namespace App\Handlers;

use GuzzleHttp\Client;
use Overtrue\Pinyin\Pinyin;
use Illuminate\Support\Str;

/**
 * slug 翻译处理程序
 */
class SlugTranslateHandler
{
    protected $api = 'http://api.fanyi.baidu.com/api/trans/vip/translate?';
    protected $appid;
    protected $secret;
    // 默认中文翻译成英文
    protected $from = 'zh';
    protected $to = 'en';

    public function __construct()
    {
        $this->appid = config('services.baidu_translate.appid');
        $this->secret = config('services.baidu_translate.secret');
    }

    // 翻译处理
    public function translate($text)
    {
        $slug = $this->baidu($text) ?: $this->pinyin($text);
        return $slug;
    }

    // 百度翻译
    public function baidu($text)
    {
        // 如果没有配置百度翻译，自动使用兼容的拼音方案
        if (empty($this->appid) || empty($this->secret)) {
            return $this->pinyin($text);
        }

        // 根据文档，生成 sign, appid+q+salt+密钥 的MD5值
        $salt = time();
        $sign = md5($this->appid . $text . $salt . $this->secret);

        // 构建请求参数
        $query = http_build_query([
            'q' => $text,
            'from' => $this->from,
            'to' => $this->to,
            'appid' => $this->appid,
            'salt' => $salt,
            'sign' => $sign,
        ]);

        // 实例化 HTTP 客户端
        $http = new Client;

        // 发送 HTTP Get 请求
        $response = $http->get($this->api . $query);

        $result = json_decode($response->getBody(), true);

        if (isset($result['trans_result'][0]['dst'])) {
            return Str::slug($result['trans_result'][0]['dst'], '-');
        }

        return false;
    }

    // 汉字转拼音
    public function pinyin($text)
    {
        return Str::slug(app(Pinyin::class)->permalink($text), '-');
    }
}
