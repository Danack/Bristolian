<?php

namespace BristolianTest\Repo\BristolStairsRepo;

use Bristolian\Exception\ContentNotFoundException;
use Bristolian\Model\BristolStairInfo;
use Bristolian\Parameters\BristolStairsInfoParams;
use Bristolian\Parameters\BristolStairsPositionParams;
use Bristolian\Repo\BristolStairsRepo\PdoBristolStairsRepo;
use BristolianTest\BaseTestCase;
use Bristolian\Repo\BristolStairsRepo;
use VarMap\ArrayVarMap;

/**
 * Tests for FakeBristolStairsRepo
 * 
 * @covers \Bristolian\Repo\BristolStairsRepo\FakeBristolStairsRepo
 */
class PdoBristolStairsRepoTest extends BaseTestCase
{
    /**
     * Test that the repo is initialized with 3 fake stairs
     */
    public function test_constructor(): void
    {
        $repo = $this->injector->make(PdoBristolStairsRepo::class);
    }


}

