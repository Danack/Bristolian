<?php

declare(strict_types = 1);

namespace Bristolian;

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

            $data[$name] = \convertToValue(/*$name,*/ $value);
        }

        return $data;
    }
}
