<?php

declare(strict_types = 1);

namespace Bristolian\Repo\BccTroRepo;

/**
 * Fake implementation of BccTroRepo for testing.
 */
class FakeBccTroRepo implements BccTroRepo
{
    /**
     * @var list<string>
     */
    private array $savedData = [];

    public function saveData(string $html): int
    {
        $this->savedData[] = $html;

        return (count($this->savedData) - 1);
    }

    public function saveDataIfNew(string $html): int|null
    {
        if ($this->savedData !== []) {
            $previousHtml = $this->savedData[array_key_last($this->savedData)];
            if ($previousHtml === $html) {
                return null;
            }
        }

        return $this->saveData($html);
    }

    public function getMostRecentData(): string|null
    {
        if ($this->savedData === []) {
            return null;
        }

        return $this->savedData[array_key_last($this->savedData)];
    }

    /**
     * @return list<string>
     */
    public function getSavedPages(): array
    {
        return $this->savedData;
    }
}
