<?php

namespace Bristolian\Service\MemeStorage;

interface MemeStorage
{
    /**
     * @param string $user_id
     * @param string $tmp_path
     * @param int $filesize
     * @param string $original_name
     * @return array{0: true, 1: null}|array{0:false, 1:string}
     */
    public function storeMemeForUser(
        string $user_id,
        string $tmp_path,
        int $filesize,
        string $original_name
    ): array;
}
