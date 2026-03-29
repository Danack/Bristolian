<?php

declare(strict_types=1);

namespace BristolianTest\Parameters;

use Bristolian\Parameters\PropertyType\LinkDescription;
use Bristolian\Parameters\PropertyType\LinkTitle;
use Bristolian\Parameters\UpdateRoomLinkParam;
use BristolianTest\BaseTestCase;
use DataType\Messages;
use VarMap\ArrayVarMap;

/**
 * @coversNothing
 */
class UpdateRoomLinkParamTest extends BaseTestCase
{
    /**
     * @return \Generator<string, array{array<string, mixed>, string|null, string|null}>
     */
    public static function provides_valid_input_and_expected_output(): \Generator
    {
        $title = str_repeat('t', LinkTitle::TITLE_MINIMUM_LENGTH);
        $description = str_repeat('d', LinkDescription::DESCRIPTION_MINIMUM_LENGTH);

        yield 'both at minimum length' => [
            ['title' => $title, 'description' => $description],
            $title,
            $description,
        ];
        yield 'both omitted' => [[], null, null];
        yield 'empty strings become null' => [['title' => '', 'description' => ''], null, null];
    }

    /**
     * @covers \Bristolian\Parameters\UpdateRoomLinkParam
     * @covers \Bristolian\Parameters\UpdateRoomLinkParam::__construct
     * @dataProvider provides_valid_input_and_expected_output
     * @param array<string, mixed> $input
     */
    public function test_createFromArray_parses_to_expected_values(
        array $input,
        ?string $expectedTitle,
        ?string $expectedDescription
    ): void {
        $params = UpdateRoomLinkParam::createFromArray($input);

        $this->assertSame($expectedTitle, $params->title);
        $this->assertSame($expectedDescription, $params->description);
    }

    /**
     * @covers \Bristolian\Parameters\UpdateRoomLinkParam
     * @dataProvider provides_valid_input_and_expected_output
     * @param array<string, mixed> $input
     */
    public function test_createFromVarMap_parses_to_expected_values(
        array $input,
        ?string $expectedTitle,
        ?string $expectedDescription
    ): void {
        $params = UpdateRoomLinkParam::createFromVarMap(new ArrayVarMap($input));

        $this->assertSame($expectedTitle, $params->title);
        $this->assertSame($expectedDescription, $params->description);
    }

    /**
     * @return \Generator<string, array{array<string, mixed>, string, string}>
     */
    public static function provides_invalid_input_and_expected_error(): \Generator
    {
        $belowTitle = LinkTitle::TITLE_MINIMUM_LENGTH - 1;
        $belowDescription = LinkDescription::DESCRIPTION_MINIMUM_LENGTH - 1;

        yield 'title too short when present' => [
            ['title' => str_repeat('t', $belowTitle), 'description' => null],
            '/title',
            Messages::STRING_TOO_SHORT,
        ];
        yield 'description too short when present' => [
            ['title' => null, 'description' => str_repeat('d', $belowDescription)],
            '/description',
            Messages::STRING_TOO_SHORT,
        ];
    }

    /**
     * @covers \Bristolian\Parameters\UpdateRoomLinkParam
     * @dataProvider provides_invalid_input_and_expected_error
     * @param array<string, mixed> $input
     */
    public function test_createFromArray_rejects_invalid_input(
        array $input,
        string $expectedPath,
        string $expectedMessage
    ): void {
        try {
            UpdateRoomLinkParam::createFromArray($input);
            $this->fail('Expected ValidationException was not thrown.');
        } catch (\DataType\Exception\ValidationException $exception) {
            $this->assertValidationProblems(
                $exception->getValidationProblems(),
                [$expectedPath => $expectedMessage]
            );
        }
    }
}
