<?php

declare(strict_types=1);

namespace BristolianTest\PHPUnit;

use PHPUnit\Event\Telemetry\HRTime;
use PHPUnit\Event\Test\Finished as TestFinished;
use PHPUnit\Event\Test\Prepared;
use PHPUnit\Event\TestRunner\Finished as TestRunnerFinished;

/**
 * Collects tests whose duration from {@see Prepared} to {@see TestFinished} exceeds the threshold.
 *
 * Note: {@see \PHPUnit\Event\Test\Passed} carries the same {@see \PHPUnit\Event\Telemetry\Info} shape,
 * but {@see \PHPUnit\Event\Telemetry\Info::durationSincePrevious()} there is only the gap since the
 * immediately preceding event, not the full test runtime.
 */
final class DanackSlowTestReportCollector
{
    private const SLOW_THRESHOLD_SECONDS = 0.01;

    /** @var array<string, HRTime> */
    private array $preparedAtTimeByTestId = [];

    /** @var list<array{seconds: float, id: string}> */
    private array $slowTests = [];

    private int $testsTimedCount = 0;

    public function onTestPrepared(Prepared $event): void
    {
        $this->preparedAtTimeByTestId[$event->test()->id()] = $event->telemetryInfo()->time();
    }

    public function onTestFinished(TestFinished $event): void
    {
        $testId = $event->test()->id();
        if (!isset($this->preparedAtTimeByTestId[$testId])) {
            return;
        }

        $preparedTime = $this->preparedAtTimeByTestId[$testId];
        unset($this->preparedAtTimeByTestId[$testId]);

        $duration = $event->telemetryInfo()->time()->duration($preparedTime);
        $this->testsTimedCount++;
        $seconds = $duration->asFloat();
        if ($seconds > self::SLOW_THRESHOLD_SECONDS) {
            $this->slowTests[] = [
                'seconds' => $seconds,
                'id' => $testId,
            ];
        }
    }

    public function writeReportAfterTestRun(TestRunnerFinished $event): void
    {
        if ($this->testsTimedCount === 0) {
            return;
        }

        if ($this->slowTests === []) {
            fwrite(
                STDOUT,
                "\nNo tests slower than " . self::SLOW_THRESHOLD_SECONDS . "s (" . $this->testsTimedCount . " timed).\n",
            );

            return;
        }

        usort(
            $this->slowTests,
            static fn (array $left, array $right): int => $right['seconds'] <=> $left['seconds'],
        );

        $lines = ["\nTests slower than " . self::SLOW_THRESHOLD_SECONDS . "s (" . count($this->slowTests) . "):\n"];
        foreach ($this->slowTests as $entry) {
            $lines[] = sprintf("  %.3fs  %s\n", $entry['seconds'], $entry['id']);
        }
        fwrite(STDOUT, implode('', $lines));
    }
}
