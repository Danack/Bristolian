<?php

namespace Bristolian\John;

class UserUploadedFile
{
    private string $tmp_name;

    private $size;

    private $name;

    /**
     * @param string $tmp_name
     * @param $size
     * @param $name
     */
    public function __construct(string $tmp_name, $size, $name)
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
     * @return mixed
     */
    public function getSize()
    {
        return $this->size;
    }

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }
}
