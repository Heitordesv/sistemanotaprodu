<?php

namespace App\Providers;

use App\Events\EcommercePaymentApproved;
use App\Listeners\ConsumeEcommerceCouponAfterPayment;
use App\Listeners\ConsumeEcommerceStockAfterPayment;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event to listener mappings for the application.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],
        EcommercePaymentApproved::class => [
            ConsumeEcommerceCouponAfterPayment::class,
            ConsumeEcommerceStockAfterPayment::class,
        ],
    ];

    public function boot()
    {
        //
    }

    public function shouldDiscoverEvents()
    {
        return false;
    }
}