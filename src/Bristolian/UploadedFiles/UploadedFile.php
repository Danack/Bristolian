<?php

namespace Bristolian\UploadedFiles;

class UploadedFile
{
    public function __construct(
        // The temporary filename of the file in which
        // the uploaded file was stored on the server.
        private string $tmp_name,
        // The size, in bytes, of the uploaded file.
        private int $size,
        // The original name of the file on the client machine.
        private string $original_name,
        // The error code associated with this file upload.
        private int $error
    ) {
        // We deliberately do not store either the 'full_path' or 'type'
        // that is submitted during the upload. Both of these are under
        // user control, and so neither should be used.
    }

    public static function fromFile(string $filename): self
    {
        $fullpath = \Safe\realpath($filename);
        return new self(
            $fullpath,
            \Safe\filesize($fullpath),
            $filename,
            0
        );
    }

    /**
     * @return string
     */
    public function getTmpName(): string
    {
        return $this->tmp_name;
    }

    /**
     * The size, in bytes, of the uploaded file.
     * @return int
     */
    public function getSize(): int
    {
        return $this->size;
    }

    /**
     * @return string
     */
    public function getOriginalName(): string
    {
        return $this->original_name;
    }

    /**
     * @return int
     */
    public function getError(): int
    {
        return $this->error;
    }
}
