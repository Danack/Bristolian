<?php

namespace Bristolian\Config;

interface EnvironmentName
{
    public function getEnvironmentNameForEmailSubject(): string;
}