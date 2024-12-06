<?php

namespace BristolianTest\AppController;

use Bristolian\AppController\System;

use BristolianTest\BaseTestCase;
use SlimDispatcher\Response\JsonResponse;
use Bristolian\Repo\DbInfo\DbInfo;
use Bristolian\Repo\DbInfo\FakeDbInfo;
use Bristolian\CSPViolation\CSPViolationStorage;
use Bristolian\CSPViolation\FakeCSPViolationStorage;

/**
 * @coversNothing
 */
class SystemTest extends BaseTestCase
{
    /**
     * @covers \Bristolian\AppController\System::index
     */
    public function testWorks_index()
    {
        $result = $this->injector->execute([System::class, 'index']);
        $this->assertIsString($result);
    }


    /**
     * @covers \Bristolian\AppController\System::showDbInfo
     */
    public function testWorks_showDbInfo()
    {
        $this->injector->alias(DbInfo::class, FakeDbInfo::class);

        $result = $this->injector->execute([System::class, 'showDbInfo']);
        $this->assertIsString($result);
    }

//    /**
//     * @covers \Bristolian\AppController\System::deploy_log
//     */
//    public function testWorks_deploy_log()
//    {
//        $result = $this->injector->execute([System::class, 'deploy_log']);
//        $this->assertIsString($result);
//    }

    /**
     * @covers \Bristolian\AppController\System::display_swagger
     */
    public function testWorks_display_swagger()
    {
        $result = $this->injector->execute([System::class, 'display_swagger']);
        $this->assertInstanceOf(JsonResponse::class, $result);
    }

    /**
     * @covers \Bristolian\AppController\System::showDbTables
     */
    public function testWorks_showDbTables()
    {
        $this->injector->alias(DbInfo::class, FakeDbInfo::class);
        $result = $this->injector->execute([System::class, 'showDbTables']);
        $this->assertIsString($result);
    }

    /**
     * @covers \Bristolian\AppController\System::showMigrationInfo
     */
    public function testWorks_showMigrationInfo()
    {
        $this->injector->alias(DbInfo::class, FakeDbInfo::class);
        $result = $this->injector->execute([System::class, 'showMigrationInfo']);
        $this->assertIsString($result);
    }

    /**
     * @covers \Bristolian\AppController\System::show_csp_reports
     */
    public function testWorks_show_csp_reports()
    {
        $this->injector->alias(CSPViolationStorage::class, FakeCSPViolationStorage::class);

        $result = $this->injector->execute([System::class, 'show_csp_reports']);
        $this->assertIsString($result);
    }
}
