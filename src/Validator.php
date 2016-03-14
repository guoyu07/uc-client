<?php

namespace VergilLai\UcClient;

use Config;

/**
 * Class Validator
 *
 * @author Vergil <vergil@vip.163.com>
 * @package VergilLai\UcClient
 */
class Validator
{

    public function usernameValidate($attribute, $value, $parameters, $validator)
    {
        $guestexp = '\xA1\xA1|\xAC\xA3|^Guest|^\xD3\xCE\xBF\xCD|\xB9\x43\xAB\xC8';
        $len = mb_strlen($value, Config::get('ucenter.charset'));

        return  !($len > 15 || $len < 3 || preg_match("/\s+|^c:\\con\\con|[%,\*\"\s\<\>\&]|$guestexp/is", $value));
    }


    public function emailValidate($attribute, $value, $parameters, $validator)
    {
        return strlen($value) > 6 && preg_match("/^[\w\-\.]+@[\w\-\.]+(\.\w+)+$/", $value);
    }



}