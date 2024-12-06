<?php

namespace Bristolian\UploadedFiles;

class FakeUploadedFiles implements UploadedFiles
{
    /**
     * @param array<non-empty-string, UploadedFile> $uploadedFiles
     */
    public function __construct(private array $uploadedFiles)
    {
    }

    public function get(string $form_name): UploadedFile|null
    {
        if (array_key_exists($form_name, $this->uploadedFiles) !== true) {
            return null;
        }

        return $this->uploadedFiles[$form_name];
    }
}
