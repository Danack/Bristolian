<?php

namespace Bristolian\Service\EmailSender;

use Bristolian\Model\Types\Email;
use Bristolian\Service\CliOutput\CliOutput;
use Mailgun\Mailgun;

class MailgunEmailClient implements EmailClient
{
    public function __construct(
        private Mailgun $mailgun,
        private CliOutput $cliOutput
    ) {
    }

    public function send(Email $email): bool
    {
        $params = [
            'from'    => 'test@mail.bristolian.org',
            'to'      => $email->recipient,
            'subject' => $email->subject,
            'text'    => $email->body
        ];

        try {
            $this->mailgun->messages()->send(
                'mail.bristolian.org',
                $params
            );
            return true;
        }
        catch (\Mailgun\Exception\HttpClientException $httpClientException) {
            $this->cliOutput->writeError("Exception: " . $httpClientException->getMessage());
        }
        return false;
    }
}
