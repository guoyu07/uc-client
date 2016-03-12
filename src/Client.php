<?php

namespace VergilLai\UcClient;

use Config;
use Request;
use Validator;
use VergilLai\UcClient\Exceptions\UcException;

class Client
{
    const UC_CLIENT_RELEASE = '20081031';

    const UC_CLIENT_VERSION = '1.6.0';

    const UC_USER_CHECK_USERNAME_FAILED = -1;
    const UC_USER_USERNAME_BADWORD = -2;
    const UC_USER_USERNAME_EXISTS = -3;
    const UC_USER_EMAIL_FORMAT_ILLEGAL = -4;
    const UC_USER_EMAIL_ACCESS_ILLEGAL = -5;
    const UC_USER_EMAIL_EXISTS = -6;

    /**
     * @var array
     */
    protected $config;


    public function __construct()
    {
        if (!defined('UC_API')) {
            $config = Config::get('ucenter');
            define('UC_CONNECT', $config['connect']);
            define('UC_DBHOST', $config['dbhost']);
            define('UC_DBUSER', $config['dbuser']);
            define('UC_DBPW', $config['dbpw']);
            define('UC_DBNAME', $config['dbname']);
            define('UC_DBCONNECT', $config['dbconnect']);
            define('UC_DBCHARSET', $config['dbcharset']);
            define('UC_DBTABLEPRE', $config['dbtablepre']);
            define('UC_KEY', $config['key']);
            define('UC_API', $config['api']);
            define('UC_CHARSET', $config['charset']);
            define('UC_IP', $config['ip']);
            define('UC_APPID', $config['appid']);
            define('UC_PPP', $config['ppp']);

            require __DIR__ . '/../uc_client/client.php';
        }
    }

    protected function apiPost($module, $action, $arg = [])
    {
        $query = http_build_query($arg);
        $postdata = $this->apiRequestData($module, $action, $query);

        $url = Config::get('ucenter.api').'/index.php';
        return $this->call($url, 500000, $postdata, '', true, Config::get('ucenter.ip'), 20);

    }

    protected function apiRequestData($module, $action, $arg = '', $extra = [])
    {
        $input = $this->apiInput($arg);

        $post = array_merge([
            'm' => $module,
            'a' => $action,
            'inajax' => 2,
            'release' => self::UC_CLIENT_RELEASE,
            'input' => $input,
            'appid' => Config::get('ucenter.appid'),
        ], $extra);
//        $post .= trim($extra);

        return $post;
    }

    protected function apiInput($data)
    {
        $s = Helper::authcode($data.'&agent='.md5($_SERVER['HTTP_USER_AGENT'])."&time=".$_SERVER['REQUEST_TIME'], 'ENCODE', Config::get('ucenter.key'));
        return $s;
    }

    protected function call($url, $limit = 0, $post = '', $cookie = '', $bysocket = false, $ip = '', $timeout = 15, $block = true)
    {
        $__times__ = Request::get('__times__', 1);
        if($__times__ > 2) {
            return '';
        }
        $url .= (false === strpos($url, '?') ? '?' : '&') . "__times__=$__times__";

        $client = new \GuzzleHttp\Client([
            'timeout'  => $timeout,
        ]);

        $header = [
            'Accept' => '*/*',
            'Accept-Language' => 'zh-cn',
            'User-Agent' => $_SERVER['HTTP_USER_AGENT'],
            'Connection' => 'Close',
            'Cookie' => $cookie,
        ];


        if ($post) {
            $header['Content-Length'] = strlen($post);
            $header['Cache-Control'] = 'no-cache';
//            $header['Content-Type'] = 'application/x-www-form-urlencoded';

            $response = $client->request('POST', $url, [
                'headers' => $header,
                'form_params' => $post,
            ]);

        } else {
            $response = $client->request('GET', $url);
        }

        if ($response->getStatusCode() == 200) {
            return $response->getBody()->getContents();
        } else {
            throw new \Illuminate\Http\Exception\HttpResponseException($response);
        }
    }

    /**
     * 用户注册
     * @param string $username      用户名
     * @param string $password      密码
     * @param string $email         电子邮件
     * @param string $questionid    安全提问索引
     * @param string $answer        安全提问答案
     * @param string $regip         注册ip
     * @return int
     */
    public function userRegister($username, $password, $email, $questionid = '', $answer = '', $regip = '')
    {
        $regip = $regip ?: Request::ip();

        $params = compact('username', 'password', 'email', 'questionid', 'answer', 'regip');

        $validator = Validator::make($params, [
            'username'  => 'uc_username',
            'email'     => 'email|min:6'
        ]);

        if ($validator->fails()) {

        }

        $uid = $this->apiPost('user', 'register', $params);

        if (!is_numeric($uid) || $uid < 0) {
            switch ($uid) {
                case self::UC_USER_CHECK_USERNAME_FAILED:
                    $message = '用户名不合法';
                    break;
                case self::UC_USER_USERNAME_BADWORD:
                    $message = '包含不允许注册的词语';
                    break;
                case self::UC_USER_USERNAME_EXISTS:
                    $message = '用户名已经存在';
                    break;
                case self::UC_USER_EMAIL_FORMAT_ILLEGAL:
                    $message = 'Email 格式有误';
                    break;
                case self::UC_USER_EMAIL_ACCESS_ILLEGAL:
                    $message = 'Email 不允许注册';
                    break;
                case self::UC_USER_EMAIL_EXISTS:
                    $message = '该 Email 已经被注册';
                    break;
                default:
                    $message = '发生未知错误';
                    break;
            }
            throw new UcException($message, $uid);
        }

        return (int)$uid;
    }

    public function userLogin($username, $password, $isuid = 0, $checkques = 0, $questionid = '', $answer = '')
    {
        $isuid = intval($isuid);
        $params = compact('username', 'password', 'isuid', 'checkques', 'questionid', 'answer');

        $return = $this->apiPost('user', 'login', $params);
        var_dump(xml_unserialize($return));

    }


}