<?php

namespace deadish;

use Bristolian\Repo\EmailQueue\EmailQueue;
use Bristolian\Service\EmailSender\EmailClient;

class StandardEmailSender implements EmailSender
{
    public function __construct(private EmailClient $emailClient)
    {

    }

    public function process(EmailQueue $emailQueue)
    {
        // get an email to process
        $email = $emailQueue->getEmailToSendAndUpdateState();
        if ($email === null) {
            return;
        }

        // try to send it
        $sent = $this->emailClient->send($email);

        // if success, set to sent
        if ($sent) {
            $emailQueue->setEmailSent($email);
            return;
        }

        if ($email->retries >= EmailSender::MAX_RETRIES) {
            $emailQueue->setEmailFailed($email);
        }
        else {
            $emailQueue->setEmailToRetry($email);
        }
    }
}