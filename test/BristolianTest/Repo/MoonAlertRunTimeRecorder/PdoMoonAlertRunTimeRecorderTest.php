<?php

namespace BristolianTest\Repo\MoonAlertRunTimeRecorder;

use Bristolian\Repo\RunTimeRecorderRepo\PdoMoonAlertRunTimeRecorder;
use BristolianTest\BaseTestCase;
use BristolianTest\Repo\TestPlaceholders;

/**
 * @covers \Bristolian\Repo\RunTimeRecorderRepo\PdoMoonAlertRunTimeRecorder
 */
class PdoMoonAlertRunTimeRecorderTest extends BaseTestCase
{
    use TestPlaceholders;

    public function testWorks()
    {
        $runTimeRecorder = $this->injector->make(PdoMoonAlertRunTimeRecorder::class);

        $runTimeRecorder->deleteAllRuns();
        $lastRunTime = $runTimeRecorder->getLastRunTime();

        $this->assertNull($lastRunTime);

        $run_id = $runTimeRecorder->startRun();

        sleep(1);
        $lastRunTime = $runTimeRecorder->getLastRunTime();

        $now = new \DateTimeImmutable();
        $this->assertLessThanOrEqual($now, $lastRunTime);

        $runTimeRecorder->setRunFinished($run_id);

        $result = $runTimeRecorder->getRunState($run_id);

        $this->assertEquals($result['id'], $run_id);
        $this->assertSame($result['status'], PdoMoonAlertRunTimeRecorder::STATE_FINISHED);
    }
}
