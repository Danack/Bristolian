<?php

namespace BristolianTest\Repo\DbInfo;

use Bristolian\Parameters\Migration;
use Bristolian\Repo\DbInfo\PdoDbInfo;
use BristolianTest\BaseTestCase;
use BristolianTest\Repo\TestPlaceholders;

/**
 * @coversNothing
 */
class PdoDbInfoTest extends BaseTestCase
{
    use TestPlaceholders;

    /**
     * @covers \Bristolian\Repo\DbInfo\PdoDbInfo
     * @return void
     * @throws \DI\InjectionException
     */
    public function testWorks()
    {
        $pdoDbInfo = $this->injector->make(PdoDbInfo::class);
        $migrations = $pdoDbInfo->getMigrations();
        $this->assertIsArray($migrations);
        $this->assertIsArray($pdoDbInfo->getTableInfo());
        foreach ($migrations as $migration) {
            $this->assertInstanceOf(Migration::class, $migration);
        }
        // TODO - any useful assertions?
    }
}
