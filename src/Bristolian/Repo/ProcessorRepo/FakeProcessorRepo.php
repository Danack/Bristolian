<?php

declare(strict_types = 1);

namespace Bristolian\Repo\ProcessorRepo;

use Bristolian\Model\Types\ProcessorState;
use Bristolian\Repo\ProcessorRepo\ProcessType;

/**
 * Fake implementation of ProcessorRepo for testing.
 */
class FakeProcessorRepo implements ProcessorRepo
{
    /**
     * @var array<value-of<ProcessType>, ProcessorState>
     */
    private array $processorStates = [];

    /**
     * @var array<value-of<ProcessType>, bool>
     */
    private array $processorEnabled = [];

    /**
     * @return array<value-of<ProcessType>, ProcessorState>
     */
    public function getProcessorsStates(): array
    {
        return $this->processorStates;
    }

    public function setProcessorEnabled(ProcessType $processor, bool $enabled): void
    {
        $this->processorEnabled[$processor->value] = $enabled;
        $this->processorStates[$processor->value] = new ProcessorState(
            id: 'fake-' . $processor->value,
            enabled: $enabled,
            type: $processor->value,
            updated_at: new \DateTimeImmutable()
        );
    }

    public function getProcessorEnabled(ProcessType $processor): bool
    {
        return $this->processorEnabled[$processor->value] ?? false;
    }
}
