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
     * @var list<BccTro[]>
     */
    private array $savedData = [];

    /**
     * @param BccTro[] $tros
     * @return int
     */
    public function saveData(array $tros): int
    {
        // For fake implementation, just store the data
        // In real implementation, this converts to JSON and stores in DB
        $this->savedData[] = $tros;

        return (count($this->savedData) - 1);
    }


    public function getMostRecentData(): BccTro|null
    {
        if ($this->savedData === []) {
            return null;
        }

        $last_batch = $this->savedData[array_key_last($this->savedData)];
        if ($last_batch === []) {
            return null;
        }

        return $last_batch[array_key_last($last_batch)];
    }
}
