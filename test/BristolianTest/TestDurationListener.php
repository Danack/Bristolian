<?php

namespace BristolianTest;


use PHPUnit\Runner\TestHook;
use PHPUnit\Runner\AfterTestHook;

/** @phpstan-ignore-next-line */
class TestDurationListener implements AfterTestHook
{
    public function executeAfterTest(string $test, float $time): void
    {
        if ($time > 0.01) {
            fwrite(STDERR, "test $test time $time \n");
        }
    }
}


