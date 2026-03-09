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
        $title = "updated title that is at least 16 chars long";
        $description = "updated description that is at least 16 chars long";

        yield 'both' => [
            ['title' => $title, 'description' => $description],
            $title,
            $description,
        ];
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
