<?php

declare(strict_types = 1);

namespace BristolianTest\Repo\FoiRequestRepo;

use Bristolian\Model\Types\FoiRequest;
use Bristolian\Parameters\FoiRequestParams;
use Bristolian\Repo\FoiRequestRepo\FakeFoiRequestRepo;
use Bristolian\Repo\FoiRequestRepo\FoiRequestRepo;
use VarMap\ArrayVarMap;

/**
 * @group standard_repo
 * @coversNothing
 */
class FakeFoiRequestRepoTest extends FoiRequestRepoFixture
{
    public function getTestInstance(): FoiRequestRepo
    {
        return new FakeFoiRequestRepo();
    }

    /**
     * @covers \Bristolian\Repo\FoiRequestRepo\FakeFoiRequestRepo::getAllFoiRequests
     */
    public function test_fake_getAllFoiRequests_returns_empty_then_created(): void
    {
        $repo = new FakeFoiRequestRepo();
        $this->assertSame([], $repo->getAllFoiRequests());

        $param = FoiRequestParams::createFromVarMap(new ArrayVarMap([
            'text' => 'One',
            'url' => 'https://example.com/1',
            'description' => 'Desc',
        ]));
        $repo->createFoiRequest($param);
        $requests = $repo->getAllFoiRequests();
        $this->assertCount(1, $requests);
        $first = current($requests);
        $this->assertInstanceOf(FoiRequest::class, $first);
    }

    /**
     * @covers \Bristolian\Repo\FoiRequestRepo\FakeFoiRequestRepo::createFoiRequest
     */
    public function test_fake_createFoiRequest_stores_and_returns_request(): void
    {
        $repo = new FakeFoiRequestRepo();
        $param = FoiRequestParams::createFromVarMap(new ArrayVarMap([
            'text' => 'Stored',
            'url' => 'https://example.com/stored',
            'description' => 'Stored desc',
        ]));

        $foiRequest = $repo->createFoiRequest($param);
        $this->assertInstanceOf(FoiRequest::class, $foiRequest);
        $this->assertSame('Stored', $foiRequest->getText());
        $this->assertNotEmpty($foiRequest->getFoiRequestId());
        $this->assertCount(1, $repo->getAllFoiRequests());
    }
}
