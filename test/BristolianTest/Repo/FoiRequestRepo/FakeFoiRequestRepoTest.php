<?php

declare(strict_types = 1);

namespace BristolianTest\Repo\FoiRequestRepo;

use Bristolian\Repo\FoiRequestRepo\FakeFoiRequestRepo;
use Bristolian\Repo\FoiRequestRepo\FoiRequestRepo;

/**
 * @group standard_repo
 */
class FakeFoiRequestRepoTest extends FoiRequestRepoTest
{
    public function getTestInstance(): FoiRequestRepo
    {
        return new FakeFoiRequestRepo();
    }
}