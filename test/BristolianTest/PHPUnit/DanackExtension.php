<?php

namespace BristolianTest\PHPUnit;

use PHPUnit\Event\Test\Failed;
use PHPUnit\Event\Test\FailedSubscriber;

use PHPUnit\Runner\Extension\Extension;
use PHPUnit\Runner\Extension\Facade;
use PHPUnit\Runner\Extension\ParameterCollection;
use PHPUnit\TextUI\Configuration\Configuration;


class DanackExtension implements Extension
{
    public function bootstrap(
        Configuration $configuration,
        Facade $facade,
        ParameterCollection $parameters
    ): void {
        $facade->registerSubscriber(new DanackFailedSubscriber());

        $slowTestCollector = new DanackSlowTestReportCollector();
        $facade->registerSubscriber(new DanackSlowTestPreparedSubscriber($slowTestCollector));
        $facade->registerSubscriber(new DanackSlowTestLifecycleFinishedSubscriber($slowTestCollector));
        $facade->registerSubscriber(new DanackSlowTestRunnerFinishedSubscriber($slowTestCollector));
    }
}
