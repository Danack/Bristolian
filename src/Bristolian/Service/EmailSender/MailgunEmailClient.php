<?php

namespace Bristolian\Service\EmailSender;

use Mailgun\Mailgun;
use Bristolian\Model\Email;

class MailgunEmailClient implements EmailClient
{
    public function __construct(private Mailgun $mailgun) {}

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
        catch (\Mailgun\Exception\HttpClientException $hce) {
            \error_log("Exception: " . $hce->getMessage());
        }
        return false;
    }

}