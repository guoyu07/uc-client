<?php

namespace VergilLai\UcClient\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * Class UcClient
 *
 * @author Vergil <vergil@vip.163.com>
 * @package VergilLai\UcClient\Facades
 */
class UcClient extends Facade
{
    protected static function getFacadeAccessor() { return 'uc-client'; }

}