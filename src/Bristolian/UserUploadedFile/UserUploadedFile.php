<?php

namespace Bristolian\UserUploadedFile;

class UserUploadedFile
{
    private string $tmp_name;

    private int $size;

    private string $name;

    /**
     * @param string $tmp_name
     * @param int $size
     * @param string $name
     */
    public function __construct(string $tmp_name, int $size, string $name)
    {
        $this->tmp_name = $tmp_name;
        $this->size = $size;
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getTmpName(): string
    {
        return $this->tmp_name;
    }

    /**
     * @return int
     */
    public function getSize(): int
    {
        return $this->size;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }
}
