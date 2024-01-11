<?php

namespace Bristolian\Model;

use Bristolian\ToArray;

class Meme
{
    private string $id;
    private string $user_id;
    private string $filename;
    private string $filetype;
    private string $filestate;

    /**
     * @param string $id
     * @param string $user_id
     * @param string $filename
     * @param string $filetype
     * @param string $filestate
     */
    public function __construct(
        string $id,
        string $user_id,
        string $filename,
        string $filetype,
        string $filestate
    ) {
        $this->id = $id;
        $this->user_id = $user_id;
        $this->filename = $filename;
        $this->filetype = $filetype;
        $this->filestate = $filestate;
    }


    /**
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getUserId(): string
    {
        return $this->user_id;
    }

    /**
     * @return string
     */
    public function getFilename(): string
    {
        return $this->filename;
    }

    /**
     * @return string
     */
    public function getFiletype(): string
    {
        return $this->filetype;
    }

    /**
     * @return string
     */
    public function getFilestate(): string
    {
        return $this->filestate;
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        $data = [];

        $data['id'] = $this->id;
        $data['user_id'] = $this->user_id;
        $data['filename'] = $this->filename;
        $data['filetype'] = $this->filetype;
        $data['filestate'] = $this->filestate;

        return $data;
    }
}
