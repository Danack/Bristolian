<?php

namespace Bristolian\MoonAlert;

class StandardMoonAlertRepo implements MoonAlertRepo
{

    /**
     * @return string[]
     */
    public function getUsersForMoonAlert(): array
    {
        return [
            "danack@basereality.com"
        ];
    }
}
