<?php

namespace Bristolian\Service\MemeStorage;

use Bristolian\UserUploadedFile\UserUploadedFile;

interface MemeStorage
{
    /**
     * @param string $user_id
     * @param UserUploadedFile $file
     * @return array{0: true, 1: null}|array{0:false, 1:string}
     */
    public function storeMemeForUser(
        string $user_id,
        UserUploadedFile $file
    ): array;
}
