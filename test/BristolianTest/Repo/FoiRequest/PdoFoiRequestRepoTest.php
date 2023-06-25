<?php

declare(strict_types = 1);

namespace BristolianTest\Params;

use Bristolian\DataType\CreateUserParams;
use BristolianTest\Repo\TestPlaceholders;
use VarMap\ArrayVarMap;
use BristolianTest\BaseTestCase;
use Bristolian\Repo\FoiRequestRepo\PdoFoiRequestRepo;
use Bristolian\DataType\FoiRequestParam;

/**
 * @group db
 * @coversNothing
 */
class PdoFoiRequestRepoTest extends BaseTestCase
{
    use TestPlaceholders;

    /**
     * @covers \Bristolian\Repo\AdminRepo\PdoAdminRepo
     */
    public function testWorks(): void
    {
        $unique = date("Ymdhis").uniqid();

        $data = [
          'text' => 'short text ' . $unique,
          'description' => 'this is a description ' . $unique,
          'url' => "http://www.example.com?unique=" . $unique,
        ];

        $varMap = new ArrayVarMap($data);

        $foiRequestParam = FoiRequestParam::createFromVarMap($varMap);
        $pdo_foi_request_repo = $this->injector->make(PdoFoiRequestRepo::class);
        $foiRequest = $pdo_foi_request_repo->createFoiRequest($foiRequestParam);

        $foiRequest_from_db = $pdo_foi_request_repo->getById($foiRequest->getFoiRequestId());

        $this->assertEquals($foiRequest, $foiRequest_from_db);
    }
}
