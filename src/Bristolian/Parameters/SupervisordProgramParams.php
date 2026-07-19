<?php

declare(strict_types=1);

namespace Bristolian\Parameters;

use Bristolian\Parameters\PropertyType\BasicString;
use Bristolian\StaticFactory;
use DataType\Create\CreateFromArray;
use DataType\Create\CreateFromRequest;
use DataType\DataType;
use DataType\GetInputTypesFromAttributes;

/**
 * Explorer-facing fields from a single Supervisord [program:...] task config file.
 */
class SupervisordProgramParams implements DataType, StaticFactory
{
    use CreateFromArray;
    use CreateFromRequest;
    use GetInputTypesFromAttributes;

    public function __construct(
        #[BasicString('program_name')]
        public readonly string $program_name,
        #[BasicString('command')]
        public readonly string $command,
    ) {
    }

    /**
     * @return array{program_name: string, command: string}
     */
    public function toExplorerArray(): array
    {
        return [
            'program_name' => $this->program_name,
            'command' => $this->command,
        ];
    }
}
