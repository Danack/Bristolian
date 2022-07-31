<?php

declare(strict_types = 1);

namespace PhpOpenDocs\JsonInput;

class FakeJsonInput implements JsonInput
{
    /** @var array */
    private $data;

    /**
     *
     * @param array $data
     */
    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function getData(): array
    {
        return $this->data;
    }
}
