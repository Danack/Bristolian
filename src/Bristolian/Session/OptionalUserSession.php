<?php

namespace Bristolian\Session;

use Bristolian\AppSession;

interface OptionalUserSession
{
    public function getAppSession(): AppSession|null;
}
