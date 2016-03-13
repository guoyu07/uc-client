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

    /**
     * 用户登录
     *
     * @param string $username      用户名 / 用户 ID / 用户 E-mail
     * @param string $password      密码
     * @param int $isuid            是否使用用户 ID登录
     *                              1:使用用户 ID登录
     *                              2:使用用户 E-mail登录
     *                              0:(默认值) 使用用户名登录
     * @param int $checkques
     * @param string $questionid
     * @param string $answer
     * @return array
     */
    public function userLogin($username, $password, $isuid = 0, $checkques = 0, $questionid = '', $answer = '')
    {
        $isuid = intval($isuid);
        $params = compact('username', 'password', 'isuid', 'checkques', 'questionid', 'answer');

        $response = $this->apiPost('user', 'login', $params);
        $response = xml_unserialize($response);

        if (0 > $response[0]) {
            switch ($response[0]) {
                case '-1':
                    throw new UcException('用户不存在，或者被删除', -1);
                    break;
                case '-2':
                    throw new UcException('密码错', -2);
                    break;
                case '-3':
                    throw new UcException('安全提问错', -3);
                    break;
                default:
                    throw new UcException('未知错误', $response[0]);
                    break;
            }
        }

        return array_combine(['uid', 'username', 'password', 'email', 'redeclare'], $response);
    }

    /**
     * 获取用户数据
     * @param string $username  用户名
     * @param int $isuid        是否使用用户 ID获取
     *                          1:使用用户 ID获取
     *                          0:(默认值) 使用用户名获取
     * @return array
     */
    public function getUser($username, $isuid = 0)
    {
        $params = compact('username', 'isuid');

        $response = xml_unserialize($this->apiPost('user', 'get_user', $params));

        if($response === null)
            throw new UcException('用户不存在');

        return array_combine(['uid', 'username', 'email'], $response);
    }

    /**
     * 更新用户资料
     * @param string $username      用户名
     * @param string $oldpw         旧密码
     * @param string $newpw         新密码，如不修改为空
     * @param string $email         Email，如不修改为空
     * @param int $ignoreoldpw      是否忽略旧密码
     *                              1:忽略，更改资料不需要验证密码
     *                              0:(默认值) 不忽略，更改资料需要验证密码
     * @param string $questionid    安全提问索引
     * @param string $answer        安全提问答案
     * @return boolean
     */
    public function userEdit($username, $oldpw, $newpw, $email, $ignoreoldpw = 0, $questionid = '', $answer = '')
    {
        $ignoreoldpw = is_bool($ignoreoldpw) ? boolval($ignoreoldpw) : $ignoreoldpw;

        $params = compact('username', 'oldpw', 'newpw', 'email', 'ignoreoldpw', 'questionid', 'answer');
        $response = $this->apiPost('user', 'edit', $params);

        if (0 >= $response) {
            switch ($response) {
                case '-1':
                    throw new UcException('旧密码不正确', -1);
                    break;
                case '-4':
                    throw new UcException('Email 格式有误', -4);
                    break;
                case '-5':
                    throw new UcException('Email 不允许注册', -5);
                    break;
                case '-6':
                    throw new UcException('该 Email 已经被注册', -6);
                    break;
                case '0':
                case '-7':
                    throw new UcException('没有做任何修改', $response);
                    break;
                case '-8':
                    throw new UcException('该用户受保护无权限更改', -8);
                    break;
                default:
                    throw new UcException('未知错误', $response);
                    break;
            }
        }

        return true;
    }

    /**
     * 删除用户
     * @param int $uid
     * @return boolean
     */
    public function userDelete($uid)
    {
        $response = $this->apiPost('user', 'delete', ['uid' => (int)$uid]);
        return (boolean)$response;
    }
}