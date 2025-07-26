<?php

namespace BristolianTest\Repo\DbInfo;

use Bristolian\Repo\DbInfo\FakeDbInfo;
use BristolianTest\BaseTestCase;
use BristolianTest\Repo\TestPlaceholders;
use Bristolian\DataType\Migration;

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
    public function testWorks()
    {
        $pdoDbInfo = new FakeDbInfo();
        $migrations = $pdoDbInfo->getMigrations();
//        $this->assertIsArray($migrations);
//        $this->assertIsArray($pdoDbInfo->getTableInfo());
        foreach ($migrations as $migration) {
            $this->assertInstanceOf(Migration::class, $migration);
        }
        // TODO - any useful assertions?
    }
}
