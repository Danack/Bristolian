<?php

declare(strict_types = 1);

namespace Bristolian\Repo\FoiRequestRepo;

use Bristolian\Model\Types\FoiRequest;
use Bristolian\Parameters\FoiRequestParams;
use Ramsey\Uuid\Uuid;

/**
 * Fake implementation of FoiRequestRepo for testing.
 */
class FakeFoiRequestRepo implements FoiRequestRepo
{
    /**
     * @var FoiRequest[]
     */
    private array $foiRequests = [];

    /**
     * @return FoiRequest[]
     */
    public function getAllFoiRequests(): array
    {
        return array_values($this->foiRequests);
    }

    public function createFoiRequest(FoiRequestParams $foiRequestParam): FoiRequest
    {
        $uuid = Uuid::uuid7();
        $id = $uuid->toString();

        $foiRequest = FoiRequest::fromParam($id, $foiRequestParam);
        $this->foiRequests[$id] = $foiRequest;

        return $foiRequest;
    }
}