<?php

namespace Bristolian\Repo\ProcessorRepo;

use Bristolian\Attribute\ReadsTable;
use Bristolian\Attribute\WritesTable;
use Bristolian\Database\processor;
use Bristolian\Model\Types\ProcessorState;

interface ProcessorRepo
{
    /**
     * @return array<value-of<ProcessType>, ProcessorState>
     */
    #[ReadsTable(processor::class)]
    public function getProcessorsStates(): array;

    #[WritesTable(processor::class)]
    public function setProcessorEnabled(ProcessType $processor, bool $enabled): void;

    #[ReadsTable(processor::class)]
    public function getProcessorEnabled(ProcessType $processor): bool;
}
