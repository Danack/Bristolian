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

    /**
     * @param BccTro[] $tros
     * @return int|null
     */
    public function saveDataIfNew(array $tros): int|null
    {
        if ($this->savedData !== []) {
            $previousTros = $this->savedData[array_key_last($this->savedData)];

            [$previousError, $previousData] = convertToValue($previousTros);
            if ($previousError !== null) {
                throw new \Exception($previousError);
            }

            [$newError, $newData] = convertToValue($tros);
            if ($newError !== null) {
                throw new \Exception($newError);
            }

            if (bccTroDataEquals($previousData, $newData)) {
                return null;
            }
        }

        return $this->saveData($tros);
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

    /**
     * @return list<BccTro[]>
     */
    public function getSavedBatches(): array
    {
        return $this->savedData;
    }
}
