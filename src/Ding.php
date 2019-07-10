<?php
namespace Ding;

use GuzzleHttp\Client;
use think\facade\Config;

class Ding
{

    protected $client;
    protected $config;
    protected $appName;
    protected $phoneArray;

    protected $hookUrl = "https://oapi.dingtalk.com/robot/send";
    public function __construct()
    {
        $this->config = Config::get('ding.');
        if (isset($this->config['appName'])) {
            $this->appName = $this->config['appName'];
        }

        $this->client = $this->createClient();
    }

    protected function createClient()
    {
        $client = new Client([
            'timeout' => 2.0,
        ]);
        return $client;
    }

    public function at($phone=[]){
        $this->phoneArray[]=$phone;
        return $this;
    }

    public function getUrl()
    {
        return $this->hookUrl . "?access_token={$this->config['accessToken']}";
    }

    public function send($content)
    {
        if (!$this->config) {
            return false;
        }
        if (empty($this->config['dingEnabled']) || $this->config['dingEnabled'] !== true || empty($this->config['accessToken'])) {
            return false;
        }

        if (is_array($content)) {
            $content = json_encode($content, JSON_UNESCAPED_UNICODE);
        } else if (is_object($content)) {
            $content = json_encode($content, JSON_UNESCAPED_UNICODE);
        }

        if (!empty($this->appName)) {
            $content = $this->appName . ':' . $content;
        }
        $atMobiles=[];
        if(!empty($this->config['atMobiles'])){
            $atMobiles=$this->config['atMobiles'];
        }
        if(!empty($this->phoneArray)){
            $atMobiles = $this->phoneArray;
        }

        $params = [
            'msgtype' => 'text',
            'text' => [
                'content' => $content,
            ],
            'at' => [
                'atMobiles' => $atMobiles,
                'isAtall' => false,
            ],
        ];
        
        $request = $this->client->post($this->getUrl(), [
            'body' => json_encode($params),
            'headers' => [
                'Content-Type' => 'application/json',
            ],
            'verify' => $this->config['ssl_verify'] ?? true,
        ]);
       
        $result = $request->getBody()->getContents();
        return json_decode($result, true);
    }
}
