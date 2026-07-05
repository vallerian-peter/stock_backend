<?php

namespace App\Providers;

use App\Events\Auth\UserLoggedIn;
use App\Events\User\UserCreated;
use App\Events\User\UserDeleted;
use App\Events\User\UserUpdated;
use App\Listeners\Auth\SendLoginAlertSms;
use App\Listeners\User\SendUserCreatedSms;
use App\Listeners\User\SendUserDeletedSms;
use App\Listeners\User\SendUserUpdatedSms;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        UserLoggedIn::class => [
            SendLoginAlertSms::class,
        ],
        UserCreated::class => [
            SendUserCreatedSms::class,
        ],
        UserUpdated::class => [
            SendUserUpdatedSms::class,
        ],
        UserDeleted::class => [
            SendUserDeletedSms::class,
        ],
    ];
}
