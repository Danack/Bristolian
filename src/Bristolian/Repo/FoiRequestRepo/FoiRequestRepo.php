<?php

namespace Bristolian\Repo\FoiRequestRepo;

use Bristolian\DataType\FoiRequestParam;
use Bristolian\Model\FoiRequest;

interface FoiRequestRepo
{
    /**
     * @return \Bristolian\Model\FoiRequest[]
     */
    public function getAllFoiRequests(): array;


    public function createFoiRequest(FoiRequestParam $foiRequestParam): FoiRequest;
}
