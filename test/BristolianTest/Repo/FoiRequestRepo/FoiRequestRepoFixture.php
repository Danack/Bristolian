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
abstract class FoiRequestRepoFixture extends BaseTestCase
{
    /**
     * Get a test instance of the FoiRequestRepo implementation.
     *
     * @return FoiRequestRepo
     */
    abstract public function getTestInstance(): FoiRequestRepo;


    /**
     * @covers \Bristolian\Repo\FoiRequestRepo\FoiRequestRepo::createFoiRequest
     */
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

    /**
     * @covers \Bristolian\Repo\FoiRequestRepo\FoiRequestRepo::getAllFoiRequests
     */
    public function test_getAllFoiRequests_returns_all_created_requests(): void
    {
        $repo = $this->getTestInstance();

        $text1 = 'Request text ' . create_test_uniqid();
        $url1 = 'https://example.com/' . create_test_uniqid();
        $description1 = 'First request ' . create_test_uniqid();

        $text2 = 'Request text ' . create_test_uniqid();
        $url2 = 'https://example.com/' . create_test_uniqid();
        $description2 = 'Second request ' . create_test_uniqid();

        $param1 = FoiRequestParams::createFromVarMap(new ArrayVarMap([
            'text' => $text1,
            'url' => $url1,
            'description' => $description1,
        ]));
        $param2 = FoiRequestParams::createFromVarMap(new ArrayVarMap([
            'text' => $text2,
            'url' => $url2,
            'description' => $description2,
        ]));

        $repo->createFoiRequest($param1);
        $repo->createFoiRequest($param2);

        $requests = $repo->getAllFoiRequests();

        $this->assertContainsOnlyInstancesOf(FoiRequest::class, $requests);

        // Find the requests by their unique strings
        $found1 = null;
        $found2 = null;
        foreach ($requests as $request) {
            if ($request->getText() === $text1) {
                $found1 = $request;
            }
            if ($request->getText() === $text2) {
                $found2 = $request;
            }
        }

        $this->assertNotNull($found1, 'First request should be found by unique text');
        $this->assertSame($text1, $found1->getText());
        $this->assertSame($url1, $found1->getUrl());
        $this->assertSame($description1, $found1->getDescription());

        $this->assertNotNull($found2, 'Second request should be found by unique text');
        $this->assertSame($text2, $found2->getText());
        $this->assertSame($url2, $found2->getUrl());
        $this->assertSame($description2, $found2->getDescription());
    }
}
