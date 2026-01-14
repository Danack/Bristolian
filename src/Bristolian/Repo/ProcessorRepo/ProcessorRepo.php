<?php

namespace Bristolian\Repo\ProcessorRepo;

use Bristolian\Model\Types\ProcessorState;

interface ProcessorRepo
{
    /**
     * @return array<value-of<ProcessType>, ProcessorState>
     */
    public function getProcessorsStates(): array;

    public function setProcessorEnabled(ProcessType $processor, bool $enabled): void;

    public function getProcessorEnabled(ProcessType $processor): bool;
}
