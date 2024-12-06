<?php

namespace Bristolian\Service\ObjectStore;

/**
 * Stores a file in the cloud.
 * This is currently Scaleway.
 */
interface FileObjectStore
{
    public function upload(string $filename, string $contents);
}
