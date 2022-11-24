<?php
/**
 * Created by PhpStorm.
 * User: andre
 * Date: 2017-06-24
 * Time: 2:17 PM
 */

namespace DanDoingDev\Ledger\Facades;


use Illuminate\Support\Facades\Facade;

class Ledger extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return \DanDoingDev\Ledger\Ledger::class;
    }
}