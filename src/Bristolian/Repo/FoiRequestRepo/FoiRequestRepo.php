<?php

namespace Bristolian\Repo\FoiRequestRepo;

use Bristolian\Attribute\ReadsTable;
use Bristolian\Attribute\WritesTable;
use Bristolian\Database\foi_requests;
use Bristolian\Model\Types\FoiRequest;
use Bristolian\Parameters\FoiRequestParams;

interface FoiRequestRepo
{
    /**
     * @return \Bristolian\Model\Types\FoiRequest[]
     */
    #[ReadsTable(foi_requests::class)]
    public function getAllFoiRequests(): array;


    #[WritesTable(foi_requests::class)]
    public function createFoiRequest(FoiRequestParams $foiRequestParam): FoiRequest;
}
