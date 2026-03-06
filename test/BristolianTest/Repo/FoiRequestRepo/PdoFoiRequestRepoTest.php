<?php

declare(strict_types = 1);

namespace BristolianTest\Repo\FoiRequestRepo;

use Bristolian\Model\Types\FoiRequest;
use Bristolian\Parameters\FoiRequestParams;
use Bristolian\Repo\FoiRequestRepo\FoiRequestRepo;
use Bristolian\Repo\FoiRequestRepo\PdoFoiRequestRepo;
use VarMap\ArrayVarMap;

/**
 * @group db
 * @coversNothing
 */
class PdoFoiRequestRepoTest extends FoiRequestRepoFixture
{
    public function getTestInstance(): FoiRequestRepo
    {
        return $this->injector->make(PdoFoiRequestRepo::class);
    }

    /**
     * @covers \Bristolian\Repo\FoiRequestRepo\PdoFoiRequestRepo::__construct
     * @covers \Bristolian\Repo\FoiRequestRepo\PdoFoiRequestRepo::getById
     */
    public function test_getById_returns_request_after_create(): void
    {
        $repo = $this->injector->make(PdoFoiRequestRepo::class);
        $param = FoiRequestParams::createFromVarMap(new ArrayVarMap([
            'text' => 'GetById test ' . create_test_uniqid(),
            'url' => 'https://example.com/foi',
            'description' => 'Description',
        ]));

        $created = $repo->createFoiRequest($param);
        $this->assertInstanceOf(FoiRequest::class, $created);

        $fetched = $repo->getById($created->getFoiRequestId());
        $this->assertInstanceOf(FoiRequest::class, $fetched);
        $this->assertSame($created->getFoiRequestId(), $fetched->getFoiRequestId());
        $this->assertSame($created->getText(), $fetched->getText());
        $this->assertSame($created->getUrl(), $fetched->getUrl());
        $this->assertSame($created->getDescription(), $fetched->getDescription());
    }
}
