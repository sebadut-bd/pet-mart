<?php

namespace App\Services\Sms;

use Illuminate\Support\Facades\Log;

/**
 * Development driver: writes the message to the log instead of sending it.
 *
 * Production needs a real gateway driver bound to SmsSender in AppServiceProvider.
 * Outside local/testing this driver refuses to "send", so OTPs are never silently lost.
 */
class LogSmsSender implements SmsSender
{
    public function send(string $phone, string $message): bool
    {
        if (! app()->environment(['local', 'testing'])) {
            Log::error('SMS not sent: no SMS gateway is configured.', ['phone_suffix' => substr($phone, -3)]);

            return false;
        }

        Log::info('[sms:log] ' . $phone . ' :: ' . $message);

        return true;
    }
}
