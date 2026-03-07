<?php

namespace BristolianTest\AppController;

use Bristolian\AppController\System;
use Bristolian\Model\TinnedFish\ValidationStatus;
use Bristolian\Parameters\TinnedFish\UpdateProductValidationStatusParams;
use Bristolian\Repo\TinnedFishProductRepo\TinnedFishProductRepo;
use Bristolian\Repo\TinnedFishProductRepo\FakeTinnedFishProductRepo;
use Bristolian\Response\TinnedFish\UpdateProductValidationStatusResponse;
use Bristolian\Service\DeployLogRenderer\DeployLogRenderer;
use Bristolian\Service\DeployLogRenderer\LocalDeployLogRenderer;
use BristolianTest\BaseTestCase;
use SlimDispatcher\Response\JsonResponse;
use Bristolian\Repo\DbInfo\DbInfo;
use Bristolian\Repo\DbInfo\FakeDbInfo;
use Bristolian\CSPViolation\CSPViolationStorage;
use Bristolian\CSPViolation\FakeCSPViolationStorage;
use VarMap\ArrayVarMap;

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

    /**
     * @covers \Bristolian\AppController\System::deploy_log
     */
    public function testWorks_deploy_log()
    {
        $this->injector->alias(DeployLogRenderer::class, LocalDeployLogRenderer::class);
        $result = $this->injector->execute([System::class, 'deploy_log']);
        $this->assertIsString($result);
        $this->assertStringContainsString('Deploy log', $result);
    }

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

    /**
     * @covers \Bristolian\AppController\System::route_explorer
     */
    public function test_route_explorer(): void
    {
        require_once __DIR__ . '/../../../app/src/app_routes.php';
        $result = $this->injector->execute([System::class, 'route_explorer']);
        $this->assertIsString($result);
        $this->assertStringContainsString('Routes', $result);
    }

    /**
     * @covers \Bristolian\AppController\System::tinned_fish_products
     */
    public function test_tinned_fish_products(): void
    {
        $this->injector->alias(TinnedFishProductRepo::class, FakeTinnedFishProductRepo::class);
        $this->injector->share(FakeTinnedFishProductRepo::class);

        $result = $this->injector->execute([System::class, 'tinned_fish_products']);
        $this->assertIsString($result);
        $this->assertStringContainsString('Tinned Fish Products', $result);
    }

    /**
     * @covers \Bristolian\AppController\System::updateProductValidationStatus
     */
    public function test_updateProductValidationStatus(): void
    {
        $this->injector->alias(TinnedFishProductRepo::class, FakeTinnedFishProductRepo::class);
        $this->injector->share(FakeTinnedFishProductRepo::class);

        $this->injector->defineParam('barcode', '1234567890');

        $params = UpdateProductValidationStatusParams::createFromVarMap(
            new ArrayVarMap(['validation_status' => ValidationStatus::VALIDATED_IS_FISH->value])
        );
        $this->injector->share($params);

        $result = $this->injector->execute([System::class, 'updateProductValidationStatus']);
        $this->assertInstanceOf(UpdateProductValidationStatusResponse::class, $result);
    }
}
