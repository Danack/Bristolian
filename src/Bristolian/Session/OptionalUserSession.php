<?php

namespace Bristolian\Session;

interface OptionalUserSession
{
    public function getAppSession(): AppSession|null;
}
