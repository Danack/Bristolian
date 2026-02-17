<?php

namespace Bristolian\CliController;

use Bristolian\Repo\EmailQueue\EmailQueue;
use Bristolian\Repo\ProcessorRepo\ProcessType;
use Bristolian\Repo\ProcessorRunRecordRepo\ProcessorRunRecordRepo;
use Bristolian\Service\EmailSender\EmailClient;
use Mailgun\Mailgun;

/**
 * Placeholder code for sending emails.
 *
 * @codeCoverageIgnore
 */
class Email
{
    const STATE_INITIAL = 'INITIAL';
    const STATE_SENDING = 'SENDING';
    const STATE_RETRY = 'RETRY';
    const STATE_FAILED = 'FAILED';
    const STATE_SENT = 'SENT';
    const STATE_SKIPPED = 'SKIPPED';


    const MAX_RETRIES = 3;

    public function __construct(
        private EmailClient $emailClient,
        private EmailQueue $emailQueue,
        private ProcessorRunRecordRepo $processorRunRecordRepo,
    ) {
    }

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

    public function clearEmailQueue(): void
    {
        $emails_skipped = $this->emailQueue->clearQueue();

        echo "Cleared email queue. $emails_skipped emails skipped.\n";
    }


    /**
     * This is a placeholder background task
     */
    public function processEmailSendQueue(): void
    {
        $callable = function () {
            $this->runInternal();
        };

        continuallyExecuteCallable(
            $callable,
            $secondsBetweenRuns = 5,
            $sleepTime = 1,
            $maxRunTime = 600
        );
    }


    public function runInternal(): void
    {

        $run_id = $this->processorRunRecordRepo->startRun(ProcessType::email_send);

        // get an email to process
        $email = $this->emailQueue->getEmailToSendAndUpdateState();

        if ($email === null) {
            echo "No email to send.\n";
            $debug_info = "No email to send.";
            goto finish;
        }

        // try to send it
        echo "Trying to send email.\n";
        $sent = $this->emailClient->send($email);

        // if success, set to sent
        if ($sent) {
            echo "Email sent.\n";
            $debug_info = "Email sent..";
            $this->emailQueue->setEmailSent($email);
            goto finish;
        }

        if ($email->retries >= self::MAX_RETRIES) {
            echo "Email failed.\n";
            $debug_info = "Email failed, and retry limit reached.\n";
            $this->emailQueue->setEmailFailed($email);
        }
        else {
            echo "Email failed, will retry.\n";
            $debug_info = "Email failed, will retry.\n";
            $this->emailQueue->setEmailToRetry($email);
        }

finish:
        $this->processorRunRecordRepo->setRunFinished($run_id, $debug_info);
    }
}
