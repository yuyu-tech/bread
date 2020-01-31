<?php

namespace YuyuTech\BREAD\Facades;

use Illuminate\Support\Facades\Facade;

class BREADFacade extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'bread';
    }
}
