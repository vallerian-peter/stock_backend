<?php

namespace App\Listeners\User;

use App\Events\User\UserDeleted;
use App\Services\Sms\SmsGateway;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class SendUserDeletedSms implements ShouldQueue
{
    use Queueable;

    public function __construct(private SmsGateway $smsGateway)
    {
    }

    public function handle(UserDeleted $event): void
    {
        $phone = $event->snapshot['phone'] ?? null;

        if (! $phone) {
            return;
        }

        $message = sprintf(
            'Hello %s, your account has been removed from the workspace.',
            $event->snapshot['full_name']
        );

        $this->smsGateway->send($phone, $message);
    }
}
