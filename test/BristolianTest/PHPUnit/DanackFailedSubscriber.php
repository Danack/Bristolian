<?php

namespace BristolianTest\PHPUnit;

use PHPUnit\Event\Test\Failed;
use PHPUnit\Event\Test\FailedSubscriber;

final class DanackFailedSubscriber implements FailedSubscriber
{
    public function notify(Failed $event): void
    {
        $throwable = $event->throwable();

        echo "PhpStormLinkSubscriber\n";
        echo $throwable->stackTrace();
        exit(-1);

//        $trace = $throwable->$stackTrace()[0] ?? null;
//
//        if (!$trace || !isset($trace['file'], $trace['line'])) {
//            return;
//        }
//
//        $file = $trace['file'];
//        $line = $trace['line'];
//
//        $url = sprintf(
//            'phpstorm://open?file=%s&line=%d',
//            $file,
//            $line
//        );
//
//        echo "\nOpen in PhpStorm: $url\n";
    }
}
