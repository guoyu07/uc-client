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
     * 本接口函数用于新用户的注册。用户名、密码、Email 为一个用户在 UCenter 的基本数据，
     * 提交后 UCenter 会按照注册设置和词语过滤的规则检测用户名和 Email 的格式是否正确合法，
     * 如果正确则返回注册后的用户 ID，否则返回相应的错误信息。
     *
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
     * 本接口函数用于用户的登录验证，用户名及密码正确无误则返回用户在 UCenter 的基本数据，
     * 否则返回相应的错误信息。如果应用程序是升级过来的，并且当前登录用户和已有用户重名，那么返回的数组中 [4] 的值将返回 1。
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
     * 本接口函数用于获取用户在 UCenter 的基本数据，如用户不存在，返回值为 integer 的数值 0。
     *
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
     * 本接口函数用于更新用户资料。更新资料需验证用户的原密码是否正确，除非指定 ignoreoldpw 为 1。
     * 如果只修改 Email 不修改密码，可让 newpw 为空；同理如果只修改密码不修改 Email，可让 email 为空。
     *
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
     * @param int|array $uid
     * @return boolean
     */
    public function userDelete($uid)
    {
        $response = $this->apiPost('user', 'delete', ['uid' => $uid]);
        return (boolean)$response;
    }

    /**
     * 删除用户头像
     * @param int|array $uid    用户名
     * @return void
     */
    public function userDeleteAvatar($uid)
    {
        $this->apiPost('user', 'deleteavatar', ['uid' => $uid]);
    }

    /**
     * 同步登录
     * 如果当前应用程序在 UCenter 中设置允许同步登录，那么本接口函数会通知其他设置了同步登录的应用程序登录，
     * 把返回的 HTML 输出在页面中即可完成对其它应用程序的通知。输出的 HTML 中包含执行远程的 javascript 脚本，
     * 请让页面在此脚本运行完毕后再进行跳转操作，否则可能会导致无法同步登录成功。同时要保证同步登录的正确有效，
     * 请保证其他应用程序的 Cookie 域和 Cookie 路径设置正确。
     *
     * @param int $uid
     * @return string 同步登录的 HTML 代码
     */
    public function userSyncLogin($uid)
    {
        return $this->apiPost('user', 'synlogin', ['uid' => intval($uid)]);
    }

    /**
     * 同步退出
     * 如果当前应用程序在 UCenter 中设置允许同步登录，那么本接口函数会通知其他设置了同步登录的应用程序退出登录，
     * 把返回的 HTML 输出在页面中即可完成其它应用程序的通知。输出的 HTML 中包含执行远程的 javascript 脚本，
     * 请让页面在此脚本运行完毕后再进行跳转操作，否则可能会导致无法同步退出登录。同时要保证同步退出登录的正确有效，
     * 请保证其他应用程序的 Cookie 域和 Cookie 路径设置正确。
     *
     * @return string 同步退出的 HTML 代码
     */
    public function userSyncLogout()
    {
        return $this->apiPost('user', 'synlogout');
    }

    /**
     * 检查 Email 地址
     * 本接口函数用于检查用户输入的 Email 的合法性。
     *
     * @param string $email Email
     * @return boolean
     */
    public function userCheckEmail($email)
    {
        $params = compact('email');
        $validator = Validator::make($params, [
            'email' => 'uc_email|email'
        ]);

        if ($validator->fails())
            throw new UcException('Email 格式有误', -4);

        $response = $this->apiPost('user', 'check_email', $params);
        if (0 > $response) {
            switch ($response) {
                case '-4':
                    throw new UcException('Email 格式有误', -4);
                    break;
                case '-5':
                    throw new UcException('Email 不允许注册', -5);
                    break;
                case '-6':
                    throw new UcException('该 Email 已经被注册', -6);
                    break;
                default:
                    throw new UcException('未知错误', $response);
                    break;
            }
        }
        return true;
    }

    /**
     * 检查用户名
     * 本接口函数用于检查用户输入的用户名的合法性。
     *
     * @param string $username
     * @return boolean
     */
    public function userCheckName($username)
    {
        $params =  compact('username');
        $validator = Validator::make($params, [
            'username' => 'uc_username'
        ]);
        if ($validator->fails())
            throw new UcException('用户名不合法', -1);


        $response = $this->apiPost('user', 'check_username', $params);
        if (0 > $response) {
            switch ($response) {
                case '-1':
                    throw new UcException('用户名不合法', -1);
                    break;
                case '-2':
                    throw new UcException('包含要允许注册的词语', -2);
                    break;
                case '-3':
                    throw new UcException('用户名已经存在', -3);
                    break;
                default:
                    throw new UcException('未知错误', $response);
                    break;
            }
        }
        return true;
    }

    /**
     * 添加保护用户
     * 本接口函数用于添加被保护的用户。
     *
     * @param string|array $username 保护用户名
     * @param string $admin 操作的管理员
     * @return boolean
     */
    public function userAddProtected($username, $admin = '')
    {
        $response = $this->apiPost('user', 'addprotected', compact('username', 'admin'));
        return $response == '1';
    }

    /**
     * 删除保护用户
     * 本接口函数用于删除被保护的用户。
     *
     * @param string $username 保护用户名
     * @return boolean
     */
    public function userDeleteProtected($username)
    {
        $response = $this->apiPost('user', 'deleteprotected', compact('username'));
        return $response == '1';
    }

    /**
     * 得到受保护的用户名列表
     * 本接口函数用于获得被保护的用户列表。
     *
     * @return array
     */
    public function userGetProtected()
    {
        $response = $this->apiPost('user', 'deleteprotected', ['1' => 1]);
        return xml_unserialize($response);
    }

    /**
     * 把重名用户合并到 UCenter
     * 本接口函数用于把重名的用户合并到 UCenter。
     *
     * @param string $oldusername   老用户名
     * @param string $newusername   新用户名
     * @param int $uid              用户 ID
     * @param string $password      密码
     * @param string $email         电子邮件
     * @return int
     */
    public function userMerge($oldusername, $newusername, $uid, $password, $email)
    {
        $response = $this->apiPost('user', 'merge', compact('oldusername', 'newusername', 'uid', 'password', 'email'));
        if (0 > $response) {
            switch ($response) {
                case '-1':
                    throw new UcException('用户名不合法', -1);
                    break;
                case '-2':
                    throw new UcException('包含不允许注册的词语', -2);
                    break;
                case '-3':
                    throw new UcException('用户名已经存在', -3);
                    break;
                default:
                    throw new UcException('未知错误', $response);
                    break;
            }
        }
        return (int)$response;
    }

    /**
     * 移除重名用户记录
     * 本接口函数用于移除重名用户记录中的指定记录，如果应用程序已处理完该重名用户，可以执行此接口函数。
     *
     * @param string $username
     */
    public function userMergeRemove($username)
    {
        $this->apiPost('user', 'merge_remove', compact('username'));
    }

    /**
     * 获取指定应用的指定用户积分
     * 本接口函数用于获取指定应用的指定用户积分。
     *
     * @param int $appid 应用 ID
     * @param int $uid 用户 ID
     * @param int $credit 积分编号
     * @return int
     */
    public function userGetCredit($appid, $uid, $credit)
    {
        $response = $this->apiPost('user', 'getcredit', compact('appid', 'uid', 'credit'));
        return (int)$response;
    }
}