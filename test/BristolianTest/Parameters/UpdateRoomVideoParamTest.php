<?php

declare(strict_types=1);

namespace BristolianTest\Parameters;

use Bristolian\Parameters\UpdateRoomVideoParam;
use BristolianTest\BaseTestCase;
use VarMap\ArrayVarMap;

/**
 * @covers \Bristolian\Parameters\UpdateRoomVideoParam
 * @covers \Bristolian\Parameters\UpdateRoomVideoParam::__construct
 */
class UpdateRoomVideoParamTest extends BaseTestCase
{
    /**
     * @return \Generator<string, array{array<string, mixed>, string|null, string|null}>
     */
    public static function provides_valid_input_and_expected_output(): \Generator
    {
        yield 'empty array' => [[], null, null];
        yield 'title only' => [['title' => 'New Title'], 'New Title', null];
        yield 'description only' => [['description' => 'New description'], null, 'New description'];
        yield 'both' => [
            ['title' => 'Updated Title', 'description' => 'Updated description'],
            'Updated Title',
            'Updated description',
        ];
        yield 'empty strings' => [['title' => '', 'description' => ''], '', ''];
    }

    /**
     * @dataProvider provides_valid_input_and_expected_output
     * @param array<string, mixed> $input
     */
    public function test_createFromArray_parses_to_expected_values(
        array $input,
        ?string $expectedTitle,
        ?string $expectedDescription
    ): void {
        $params = UpdateRoomVideoParam::createFromArray($input);

        $this->assertSame($expectedTitle, $params->title);
        $this->assertSame($expectedDescription, $params->description);
    }

    /**
     * @dataProvider provides_valid_input_and_expected_output
     * @param array<string, mixed> $input
     */
    public function test_createFromVarMap_parses_to_expected_values(
        array $input,
        ?string $expectedTitle,
        ?string $expectedDescription
    ): void {
        $params = UpdateRoomVideoParam::createFromVarMap(new ArrayVarMap($input));

        $this->assertSame($expectedTitle, $params->title);
        $this->assertSame($expectedDescription, $params->description);
    }
}
