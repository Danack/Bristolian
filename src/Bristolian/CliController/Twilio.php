<?php

declare(strict_types = 1);

namespace Bristolian\CliController;

use Bristolian\Service\TwilioClient;

class Twilio
{
    public function test(
        TwilioClient $twilioClient,
        string $number,
        string $message
    ) {
        $twilioClient->sendMessage($number, $message);
    }
}
