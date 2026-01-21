<?php

declare(strict_types = 1);

namespace BristolianTest\Repo\FoiRequestRepo;

use Bristolian\Model\Types\FoiRequest;
use Bristolian\Parameters\FoiRequestParams;
use Bristolian\Repo\FoiRequestRepo\FoiRequestRepo;
use BristolianTest\BaseTestCase;
use VarMap\ArrayVarMap;

/**
 * Abstract test class for FoiRequestRepo implementations.
 */
abstract class FoiRequestRepoTest extends BaseTestCase
{
    /**
     * Get a test instance of the FoiRequestRepo implementation.
     *
     * @return FoiRequestRepo
     */
    abstract public function getTestInstance(): FoiRequestRepo;


    public function test_getAllFoiRequests_returns_empty_initially(): void
    {
        $repo = $this->getTestInstance();

        $requests = $repo->getAllFoiRequests();

        $this->assertIsArray($requests);
        $this->assertEmpty($requests);
    }


    public function test_createFoiRequest_creates_and_stores_request(): void
    {
        $repo = $this->getTestInstance();

        $foiRequestParam = FoiRequestParams::createFromVarMap(new ArrayVarMap([
            'text' => 'Test FOI request',
            'url' => 'https://example.com',
            'description' => 'A test FOI request',
        ]));

        $foiRequest = $repo->createFoiRequest($foiRequestParam);

        $this->assertInstanceOf(FoiRequest::class, $foiRequest);
        $this->assertSame('Test FOI request', $foiRequest->getText());
        $this->assertSame('https://example.com', $foiRequest->getUrl());
    }


    public function test_getAllFoiRequests_returns_all_created_requests(): void
    {
        $repo = $this->getTestInstance();

        $param1 = FoiRequestParams::createFromVarMap(new ArrayVarMap([
            'text' => 'Request 1',
            'url' => 'https://example.com/1',
            'description' => 'First request',
        ]));
        $param2 = FoiRequestParams::createFromVarMap(new ArrayVarMap([
            'text' => 'Request 2',
            'url' => 'https://example.com/2',
            'description' => 'Second request',
        ]));

        $repo->createFoiRequest($param1);
        $repo->createFoiRequest($param2);

        $requests = $repo->getAllFoiRequests();

        $this->assertCount(2, $requests);
        $this->assertContainsOnlyInstancesOf(FoiRequest::class, $requests);
    }
}
