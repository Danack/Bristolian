<?php

declare(strict_types = 1);

namespace BristolianTest\Repo\FoiRequestRepo;

use Bristolian\Repo\FoiRequestRepo\FakeFoiRequestRepo;
use Bristolian\Repo\FoiRequestRepo\FoiRequestRepo;

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
}
