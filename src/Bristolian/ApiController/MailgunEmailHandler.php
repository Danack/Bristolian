<?php

namespace Bristolian\ApiController;

class MailgunEmailHandler
{

    // https://bristolian.org/api/mail

    public function handleIncomingEmail(): void
    {
    }

//200 (Success) When Mailgun receives this code, it will determine the webhook POST is successful and will not be retried.
//406 (Not Applicable)  When this code is received, Mailgun will determine the POST is rejected and it will not be retried.
//Any other code    Mailgun will try POSTing according to the schedule (below) for webhooks other than the delivery notification.
}
