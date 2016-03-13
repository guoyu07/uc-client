<?php

return [

    'connect' => env('UC_CONNECT', 'mysql'),

    'dbhost' => env('UC_DBHOST', 'localhost'),

    'dbuser' => env('UC_DBUSER'),

    'dbpw' => env('UC_DBPW'),

    'dbname' => env('UC_DBNAME'),

    'dbcharset' => env('UC_DBCHARSET', 'utf8'),

    'dbtablepre' => env('UC_DBTABLEPRE', 'uc_'),

    'dbconnect' => env('UC_DBCONNECT', 0),

    'key' => env('UC_KEY'),

    'api' => env('UC_API'),

    'charset' => env('UC_CHARSET', 'utf-8'),

    'ip' => env('UC_IP'),

    'appid' => env('UC_APPID'),

    'ppp' => env('UC_PPP'),

];