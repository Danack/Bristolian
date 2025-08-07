<?php

namespace Bristolian\Repo\FoiRequestRepo;

use Bristolian\Parameters\FoiRequestParams;
use Bristolian\Model\FoiRequest;

interface FoiRequestRepo
{
    /**
     * @return \Bristolian\Model\FoiRequest[]
     */
    public function getAllFoiRequests(): array;


    public function createFoiRequest(FoiRequestParams $foiRequestParam): FoiRequest;
}
