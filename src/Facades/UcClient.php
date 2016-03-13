<?php

namespace VergilLai\UcClient\Facades;

use Illuminate\Support\Facades\Facade;

class UcClient extends Facade
{

    protected static function getFacadeAccessor() { return 'uc-client'; }

}