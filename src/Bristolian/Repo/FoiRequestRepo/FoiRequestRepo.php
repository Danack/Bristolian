<?php

namespace Bristolian\Repo\FoiRequestRepo;

use Bristolian\Model\Types\FoiRequest;
use Bristolian\Parameters\FoiRequestParams;

interface FoiRequestRepo
{
    /**
     * @return \Bristolian\Model\Types\FoiRequest[]
     */
    public function getAllFoiRequests(): array;


    public function createFoiRequest(FoiRequestParams $foiRequestParam): FoiRequest;
}
