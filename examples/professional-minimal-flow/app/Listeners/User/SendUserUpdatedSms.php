<?php

namespace App\Listeners\User;

use App\Events\User\UserUpdated;
use App\Services\Sms\SmsGateway;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class SendUserUpdatedSms implements ShouldQueue
{
    use Queueable;

    public function __construct(private SmsGateway $smsGateway)
    {
    }

    public function handle(UserUpdated $event): void
    {
        if (! $event->user->phone) {
            return;
        }

        $message = sprintf(
            'Hello %s, your profile was updated successfully.',
            $event->user->first_name
        );

        $this->smsGateway->send($event->user->phone, $message);
    }
}
