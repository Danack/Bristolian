<?php

declare(strict_types = 1);

namespace BristolianTest\Repo\FoiRequestRepo;

use Bristolian\Repo\FoiRequestRepo\FoiRequestRepo;
use Bristolian\Repo\FoiRequestRepo\PdoFoiRequestRepo;

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
}
