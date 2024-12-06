<?php

namespace Bristolian\UploadedFiles;

interface UploadedFiles
{
    public function get(string $form_name): UploadedFile|null;
}
