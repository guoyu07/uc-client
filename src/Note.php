<?php

namespace VergilLai\UcClient;

use Config;
use Cache;
use Request;
use VergilLai\UcClient\Contracts\UcenterNoteApi;

/**
 * Class Note
 *
 * @author Vergil <vergil@vip.163.com>
 * @package VergilLai\UcClient
 */
class Note implements UcenterNoteApi
{

    protected $config;

    public function __construct()
    {
        $this->config = Config::get('ucenter');
    }

    public function test(array $get, array $post)
    {
        return self::API_RETURN_SUCCEED;
    }

    public function deleteuser(array $get, array $post)
    {
        $uids = $get['ids'];
        if (!$this->config['api_deleteuser'])
            return self::API_RETURN_FORBIDDEN;

        /**
         * @todo 删除用户代码
         */

        return self::API_RETURN_SUCCEED;
    }

    public function renameuser(array $get, array $post)
    {
        $uid = $get['uid'];
        $usernameold = $get['oldusername'];
        $usernamenew = $get['newusername'];
        if(!$this->config['api_renameuser']) {
            return self::API_RETURN_FORBIDDEN;
        }

        /**
         * @todo 重名用户代码
         */

        return self::API_RETURN_SUCCEED;
    }

    public function gettag(array $get, array $post)
    {
        $name = $get['id'];
        if(!$this->config['api_gettag']) {
            return self::API_RETURN_FORBIDDEN;
        }

        $return = [];
        return xml_serialize($return, true);
    }

    public function synlogin(array $get, array $post)
    {
        $uid = $get['uid'];
        $username = $get['username'];
        if(!$this->config['api_synlogin']) {
            return self::API_RETURN_FORBIDDEN;
        }

        header('P3P: CP="CURa ADMa DEVa PSAo PSDo OUR BUS UNI PUR INT DEM STA PRE COM NAV OTC NOI DSP COR"');

        setcookie('Example_auth', Helper::authcode($uid."\t".$username, 'ENCODE'), 0, $this->config['cookie_path'],
            $this->config['cookie_domain'], Request::server('SERVER_PORT') == 433);
    }

    public function synlogout(array $get, array $post)
    {
        $uid = $get['uid'];
        $username = $get['username'];
        if(!$this->config['api_synlogout']) {
            return self::API_RETURN_FORBIDDEN;
        }

        header('P3P: CP="CURa ADMa DEVa PSAo PSDo OUR BUS UNI PUR INT DEM STA PRE COM NAV OTC NOI DSP COR"');
        _setcookie('Example_auth', _authcode($uid."\t".$username, 'ENCODE'));

        setcookie('Example_auth', '', -86400 * 365, $this->config['cookie_path'],
            $this->config['cookie_domain'], Request::server('SERVER_PORT') == 433);
    }

    public function updatepw(array $get, array $post)
    {
        if(!$this->config['api_updatepw']) {
            return self::API_RETURN_FORBIDDEN;
        }
        $username = $get['username'];
        $password = $get['password'];

        /**
         * @todo 更新密码代码
         */

        return self::API_RETURN_SUCCEED;
    }

    public function updatebadwords(array $get, array $post)
    {
        if (!$this->config['api_updatebadword']) {
            return self::API_RETURN_FORBIDDEN;
        }

        $data = [];
        foreach($post as $k => $v) {
            $data['findpattern'][$k] = $v['findpattern'];
            $data['replace'][$k] = $v['replacement'];
        }
        Cache::forever(self::BADWORDS_CACHE_KEY, $data);

        return self::API_RETURN_SUCCEED;
    }

    public function updatehosts(array $get, array $post)
    {
        if(!$this->config['api_updatehosts']) {
            return self::API_RETURN_FORBIDDEN;
        }

        Cache::forever(self::HOSTS_CACHE_KEY, $post);

        return self::API_RETURN_SUCCEED;
    }

    public function updateapps(array $get, array $post)
    {
        if(!$this->config['api_updateapps']) {
            return self::API_RETURN_FORBIDDEN;
        }

        $UC_API = $post['UC_API'];

        Cache::forever(self::APPS_CACHE_KEY, $post);

        /**
         * @todo 修改配置文件
         */

        return self::API_RETURN_SUCCEED;
    }

    public function updateclient(array $get, array $post)
    {
        if(!$this->config['api_updateclient']) {
            return self::API_RETURN_FORBIDDEN;
        }

        Cache::forever(self::SETTINGS_CACHE_KEY, $post);
    }

    public function updatecredit(array $get, array $post)
    {
        if(!$this->config['api_updatecredit']) {
            return API_RETURN_FORBIDDEN;
        }
        $credit = $get['credit'];
        $amount = $get['amount'];
        $uid = $get['uid'];

        /**
         * @todo 更新用户积分代码
         */

        return self::API_RETURN_SUCCEED;
    }

    public function getcredit(array $get, array $post)
    {
        if(!$this->config['api_getcredit']) {
            return self::API_RETURN_FORBIDDEN;
        }

        /**
         * @todo return 用户积分
         */
    }

    public function getcreditsettings(array $get, array $post)
    {
        if(!$this->config['api_getcreditsettings']) {
            return self::API_RETURN_FORBIDDEN;
        }
        $credits = [];
        return xml_serialize($credits);
    }

    public function updatecreditsettings(array $get, array $post)
    {
        if(!$this->config['api_updatecreditsettings']) {
            return self::API_RETURN_FORBIDDEN;
        }

        /**
         * @todo 更新积分设置代码
         */

        return self::API_RETURN_SUCCEED;
    }


}