<?php

declare(strict_types = 1);

namespace Bristolian\Repo\BccTroRepo;

use Bristolian\Model\Types\BccTro;

/**
 * Fake implementation of BccTroRepo for testing.
 */
class FakeBccTroRepo implements BccTroRepo
{
    /**
     * @var array<string, mixed>
     */
    private array $savedData = [];

    /**
     * @param BccTro[] $tros
     * @return void
     */
    public function saveData(array $tros): void
    {
        // For fake implementation, just store the data
        // In real implementation, this converts to JSON and stores in DB
        $this->savedData[] = $tros;
    }
}