<?php

namespace Bristolian\UploadedFiles;

/**
 * A class to hide wrap the $_FILES functionality in a layer of
 * abstraction.
 */
interface UploadedFiles
{
    public function get(string $form_name): UploadedFile|null;
}
