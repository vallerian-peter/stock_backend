<?php

namespace App\Listeners\User;

use App\Events\User\UserCreated;
use App\Services\Sms\SmsGateway;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class SendUserCreatedSms implements ShouldQueue
{
    use Queueable;

    public function __construct(private SmsGateway $smsGateway)
    {
    }

    public function handle(UserCreated $event): void
    {
        if (! $event->user->phone) {
            return;
        }

        $message = sprintf(
            'Hello %s, your account has been created successfully.',
            $event->user->first_name
        );

        $this->smsGateway->send($event->user->phone, $message);
    }
}
