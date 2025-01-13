<?php

namespace deadish;

interface EmailSender
{
    const STATE_INITIAL = 'INITIAL';
    const STATE_SENDING = 'SENDING';
    const STATE_RETRY = 'RETRY';
    const STATE_FAILED = 'FAILED';
    const STATE_SENT = 'FAILED';

    const MAX_RETRIES = 3;

}

