<?php

namespace Bristolian\Service\Mailgun;

use VarMap\VarMap;

interface PayloadValidator
{
    public function validate(VarMap $payload): bool;
}
