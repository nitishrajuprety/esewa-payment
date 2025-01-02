<?php

namespace NitishRajUprety\EsewaPayment;

use Illuminate\Support\ServiceProvider;

class EsewaPaymentServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->singleton('esewa-payment', EsewaPayment::class);
    }

    public function boot(): void
    {
        $this->publishes([
            __DIR__."/config/esewa-payment.php" => config_path("esewa-payment.php"),
        ]);
    }
}
