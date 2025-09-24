<?php

namespace Bristolian\UploadedFiles;

/**
 * A class to hide wrap the $_FILES functionality in a layer of
 * abstraction.
 *
 * Standard implementation to be used on server.
 */
class ServerFilesUploadedFiles implements UploadedFiles
{
    public function get(string $form_name): UploadedFile|null
    {
        if (array_key_exists($form_name, $_FILES) !== true) {
            return null;
        }

        $file_entry = $_FILES[$form_name];

        $required_keys = [
            "tmp_name",
            "size",
            "name",
            "error"
        ];

        foreach ($required_keys as $required_key) {
            if (array_key_exists($required_key, $file_entry) !== true) {
                // TODO - logging
                return null;
            }
        }

        return new UploadedFile(
            $file_entry["tmp_name"],
            $file_entry["size"],
            $file_entry["name"],
            $file_entry["error"],
        );
    }
}
