<?php

namespace Bristolian\Session;

use Bristolian\Session\AppSession;

interface OptionalUserSession
{
    public function getAppSession(): AppSession|null;
}
