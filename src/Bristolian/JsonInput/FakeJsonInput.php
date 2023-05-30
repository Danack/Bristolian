<?php

declare(strict_types = 1);

namespace Bristolian\JsonInput;

class FakeJsonInput implements JsonInput
{
    /** @var mixed[] */
    private $data;

    /**
     *
     * @param mixed[] $data
     */
    public function __construct(array $data)
    {
        $this->data = $data;
    }

    /**
     * @return mixed[]
     */
    public function getData(): array
    {
        return $this->data;
    }
}
