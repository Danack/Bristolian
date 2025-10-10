<?php

namespace Bristolian\UploadedFiles;

/**
 * A class to hold information about an uploaded file.
 */
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
    public function getErrorCode(): int
    {
        return $this->error;
    }

    public function getErrorMessage(): string
    {
        $phpFileUploadErrors = array(
            0 => 'There is no error, the file uploaded with success',
            1 => 'The uploaded file exceeds the upload_max_filesize directive in php.ini',
            2 => 'The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form',
            3 => 'The uploaded file was only partially uploaded',
            4 => 'No file was uploaded',
            6 => 'Missing a temporary folder',
            7 => 'Failed to write file to disk.',
            8 => 'A PHP extension stopped the file upload.',
        );

        if (array_key_exists($this->error, $phpFileUploadErrors) === true) {
            return $phpFileUploadErrors[$this->error];
        }

        // @codeCoverageIgnoreStart
        return "Unknown file upload error code: " . $this->error;
        // @codeCoverageIgnoreEnd
    }
}
