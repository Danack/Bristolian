<?php

namespace Bristolian\MoonAlert;

interface MoonAlertRepo
{
    /**
     * @return string[]
     */
    public function getUsersForMoonAlert(): array;
}
