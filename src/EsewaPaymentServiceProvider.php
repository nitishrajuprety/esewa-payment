<?php

namespace NitishRajUprety\EsewaPayment;

use Illuminate\Support\ServiceProvider;

class EsewaPaymentServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton('esewa-payment', fn() => $this->app->make(EsewaPayment::class));
    }

    public function boot(): void
    {
        $this->publishes([
            __DIR__."/config/esewa-payment.php" => config_path("esewa-payment.php"),
        ]);
    }
}
