<?php

declare(strict_types = 1);

namespace BristolianTest\Params;

use Bristolian\Parameters\CreateUserParams;
use BristolianTest\Repo\TestPlaceholders;
use VarMap\ArrayVarMap;
use BristolianTest\BaseTestCase;
//use Bristolian\Repo\AdminRepo\PdoAdminRepo;
use Bristolian\Repo\FoiRequestRepo\PdoFoiRequestRepo;
use Bristolian\Parameters\FoiRequestParams;

/**
 * @coversNothing
 */
class PdoFoiRequestRepoTest extends BaseTestCase
{
    use TestPlaceholders;

    /**
     * @covers \Bristolian\Repo\AdminRepo\PdoAdminRepo
     * @group slow
     * This is really slow for some reason...
     */
    public function testWorks(): void
    {
        $this->markTestSkipped("TODO - fix");

        $unique = date("Ymdhis").uniqid();

        $data = [
          'text' => 'short text ' . $unique,
          'description' => 'this is a description ' . $unique,
          'url' => "http://www.example.com?unique=" . $unique,
        ];

        $varMap = new ArrayVarMap($data);

        $foiRequestParam = FoiRequestParams::createFromVarMap($varMap);
        $pdo_foi_request_repo = $this->injector->make(PdoFoiRequestRepo::class);
        $foiRequest = $pdo_foi_request_repo->createFoiRequest($foiRequestParam);

        $foiRequest_from_db = $pdo_foi_request_repo->getById($foiRequest->getFoiRequestId());

        $this->assertEquals($foiRequest, $foiRequest_from_db);
    }
}
