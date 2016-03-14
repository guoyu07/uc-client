<?php

namespace VergilLai\UcClient;

use Config,Request;
use VergilLai\UcClient\Contracts\UcenterNoteApi;

/**
 * Class Controller
 *
 * @author Vergil <vergil@vip.163.com>
 * @package VergilLai\UcClient
 */
class Controller extends \App\Http\Controllers\Controller
{
    const API_RETURN_SUCCEED = 1;
    const API_RETURN_FAILED = -1;
    const API_RETURN_FORBIDDEN = -2;

    public function api(UcenterNoteApi $note)
    {
        $code = Request::get('code');

        parse_str(Helper::authcode($code, 'DECODE', Config::get('ucenter.key')), $get);


        Request::server('REQUEST_TIME') - $get['time'] > 3600 && exit('Authracation has expiried');
        empty($get) && exit('Invalid Request');

        $action = $get['action'];

        $_input = file_get_contents('php://input');
        $post = $_input ? xml_unserialize($_input) : [];

        $allowActions = [
            'test', 'deleteuser', 'renameuser', 'gettag', 'synlogin', 'synlogout', 'updatepw', 'updatebadwords',
            'updatehosts', 'updateapps', 'updateclient', 'updatecredit', 'getcreditsettings', 'updatecreditsettings', 'getcredit'
        ];

        if (in_array($action, $allowActions)) {
            $return = call_user_func([$note, $action], $get, $post);
            return response($return);
        } else {
            return response(self::API_RETURN_FAILED);
        }
    }
}