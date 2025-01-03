<?php

namespace Bristolian\CliController;

use Mailgun\Mailgun;

/**
 * Placeholder code for sending emails.
 *
 * @codeCoverageIgnore
 */
class Email
{
    public function testEmail(Mailgun $mailgun): void
    {
        $params = [
            'from'    => 'test@mail.bristolian.org',
            'to'      => 'Danack@basereality.com',
            'subject' => 'The PHP SDK is awesome!',
            'text'    => 'It is so simple to send a message.'
        ];

        try {
            $mailgun->messages()->send(
                'mail.bristolian.org',
                $params
            );
        }
        catch (\Mailgun\Exception\HttpClientException $hce) {
            echo "Exception: " . $hce->getMessage();
        }
    }
}
