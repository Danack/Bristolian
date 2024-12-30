<?php

namespace Bristolian\Session;

use Bristolian\AppSession;

class StandardOptionalUserSession implements OptionalUserSession
{
    public function __construct(private AppSession|null $appSession)
    {
    }

    public function getAppSession(): AppSession|null
    {
        return $this->appSession;
    }
}