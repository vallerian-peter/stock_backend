<?php

namespace App\Listeners\Auth;

use App\Events\Auth\UserLoggedIn;
use App\Services\Sms\SmsGateway;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class SendLoginAlertSms implements ShouldQueue
{
    use Queueable;

    public function __construct(private SmsGateway $smsGateway)
    {
    }

    public function handle(UserLoggedIn $event): void
    {
        if (! $event->user->phone) {
            return;
        }

        $message = sprintf(
            'Hello %s, your account was just accessed successfully.',
            $event->user->first_name
        );

        $this->smsGateway->send($event->user->phone, $message);
    }
}
