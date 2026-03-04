<?php

declare(strict_types=1);

namespace BristolianTest\Parameters;

use Bristolian\Parameters\SetEntityTagsParam;
use BristolianTest\BaseTestCase;

/**
 * @coversNothing
 */
class SetEntityTagsParamTest extends BaseTestCase
{
    /**
     * @return \Generator<string, array{array<string, mixed>, string[]}>
     */
    public static function provides_valid_input_and_expected_tag_ids(): \Generator
    {
        yield 'empty tag_ids' => [['tag_ids' => []], []];
        yield 'single tag' => [['tag_ids' => ['id-1']], ['id-1']];
        yield 'multiple tags' => [['tag_ids' => ['id-1', 'id-2', 'id-3']], ['id-1', 'id-2', 'id-3']];
        yield 'missing tag_ids defaults to empty' => [[], []];
        yield 'non-array tag_ids treated as empty' => [['tag_ids' => 'not-array'], []];
        yield 'mixed array only strings kept' => [
            ['tag_ids' => ['id-1', 123, null, 'id-2', [], 'id-3']],
            ['id-1', 'id-2', 'id-3'],
        ];
    }

    /**
     * @covers \Bristolian\Parameters\SetEntityTagsParam
     * @covers \Bristolian\Parameters\SetEntityTagsParam::__construct
     * @covers \Bristolian\Parameters\SetEntityTagsParam::fromArray
     * @dataProvider provides_valid_input_and_expected_tag_ids
     * @param array<string, mixed> $input
     * @param string[] $expectedTagIds
     */
    public function test_fromArray_parses_input_to_expected_tag_ids(array $input, array $expectedTagIds): void
    {
        $params = SetEntityTagsParam::fromArray($input);

        $this->assertSame($expectedTagIds, $params->tag_ids);
    }
}
