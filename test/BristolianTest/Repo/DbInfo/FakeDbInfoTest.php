<?php

namespace BristolianTest\Repo\DbInfo;

use Bristolian\Model\Types\MigrationThatHasBeenRun;
use Bristolian\Repo\DbInfo\FakeDbInfo;
use BristolianTest\BaseTestCase;
use BristolianTest\Repo\TestPlaceholders;

/**
 * @coversNothing
 */
class FakeDbInfoTest extends BaseTestCase
{
    use TestPlaceholders;

    /**
     * @covers \Bristolian\Repo\DbInfo\FakeDbInfo
     * @return void
     * @throws \DI\InjectionException
     */
    /**
     * @covers \Bristolian\Repo\DbInfo\FakeDbInfo::getTableInfo
     * @covers \Bristolian\Repo\DbInfo\FakeDbInfo::getMigrations
     */
    public function testWorks()
    {
        $pdoDbInfo = new FakeDbInfo();
        $migrations = $pdoDbInfo->getMigrations();
        foreach ($migrations as $migration) {
            $this->assertInstanceOf(MigrationThatHasBeenRun::class, $migration);
        }
        $tables = $pdoDbInfo->getTableInfo();
        $this->assertCount(2, $tables);
    }
}
