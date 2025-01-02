<?php

namespace NitishRajUprety\EsewaPayment;

use Illuminate\Support\Facades\Facade;

class EsewaPaymentFacade extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'esewa-payment';
    }
}
