<?php

declare(strict_types = 1);

namespace Bristolian;

use Bristolian\Exception\BristolianException;

trait ToArray
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        $data = [];
        foreach ($this as $name => $value) {
            if (strpos($name, '__') === 0) {
                //Skip
                continue;
            }

            [$error, $result] = \convertToValue(/*$name,*/ $value);

            if ($error !== null) {
                throw new BristolianException("Failed to convert object to array on item [$name]");
            }

            $data[$name] = $result;
        }

        return $data;
    }
}
