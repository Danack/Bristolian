<?php

declare(strict_types=1);

namespace BristolianTest\Parameters;

use Bristolian\Parameters\PropertyType\AboutMeText;
use Bristolian\Parameters\PropertyType\RoomName;
use Bristolian\Parameters\UpdateRoomDetailsParam;
use BristolianTest\BaseTestCase;
use DataType\Messages;
use VarMap\ArrayVarMap;

/**
 * @coversNothing
 */
class UpdateRoomDetailsParamTest extends BaseTestCase
{
    private static function validName(): string
    {
        return 'Valid room name for details';
    }

    /**
     * @return \Generator<string, array{array<string, mixed>, string, string}>
     */
    public static function provides_valid_input_and_expected_output(): \Generator
    {
        $name = self::validName();

        yield 'name and purpose' => [
            ['name' => $name, 'purpose' => 'Room purpose text'],
            $name,
            'Room purpose text',
        ];
        yield 'empty purpose allowed' => [
            ['name' => $name, 'purpose' => ''],
            $name,
            '',
        ];
    }

    /**
     * @covers \Bristolian\Parameters\UpdateRoomDetailsParam
     * @covers \Bristolian\Parameters\UpdateRoomDetailsParam::__construct
     * @covers \Bristolian\Parameters\PropertyType\RoomName
     * @covers \Bristolian\Parameters\PropertyType\AboutMeText
     * @dataProvider provides_valid_input_and_expected_output
     * @param array<string, mixed> $input
     */
    public function test_createFromArray_parses_to_expected_values(
        array $input,
        string $expectedName,
        string $expectedPurpose
    ): void {
        $params = UpdateRoomDetailsParam::createFromArray($input);

        $this->assertSame($expectedName, $params->name);
        $this->assertSame($expectedPurpose, $params->purpose);
    }

    /**
     * @covers \Bristolian\Parameters\UpdateRoomDetailsParam
     * @covers \Bristolian\Parameters\PropertyType\RoomName
     * @covers \Bristolian\Parameters\PropertyType\AboutMeText
     * @dataProvider provides_valid_input_and_expected_output
     * @param array<string, mixed> $input
     */
    public function test_createFromVarMap_parses_to_expected_values(
        array $input,
        string $expectedName,
        string $expectedPurpose
    ): void {
        $params = UpdateRoomDetailsParam::createFromVarMap(new ArrayVarMap($input));

        $this->assertSame($expectedName, $params->name);
        $this->assertSame($expectedPurpose, $params->purpose);
    }

    /**
     * @return \Generator<string, array{array<string, mixed>, string, string}>
     */
    public static function provides_invalid_input_and_expected_error(): \Generator
    {
        $name = self::validName();

        yield 'missing name' => [
            ['purpose' => 'Some purpose'],
            '/name',
            Messages::VALUE_NOT_SET,
        ];
        yield 'missing purpose' => [
            ['name' => $name],
            '/purpose',
            Messages::VALUE_NOT_SET,
        ];
        yield 'name too short' => [
            ['name' => str_repeat('x', RoomName::MINIMUM_LENGTH - 1), 'purpose' => 'ok'],
            '/name',
            Messages::STRING_TOO_SHORT,
        ];
        yield 'name too long' => [
            ['name' => str_repeat('n', RoomName::MAXIMUM_LENGTH + 1), 'purpose' => 'ok'],
            '/name',
            Messages::STRING_TOO_LONG,
        ];
        yield 'purpose too long' => [
            ['name' => $name, 'purpose' => str_repeat('p', AboutMeText::MAXIMUM_ABOUT_ME_LENGTH + 1)],
            '/purpose',
            Messages::STRING_TOO_LONG,
        ];
    }

    /**
     * @covers \Bristolian\Parameters\UpdateRoomDetailsParam
     * @covers \Bristolian\Parameters\PropertyType\RoomName
     * @covers \Bristolian\Parameters\PropertyType\AboutMeText
     * @dataProvider provides_invalid_input_and_expected_error
     * @param array<string, mixed> $input
     */
    public function test_createFromArray_rejects_invalid_input(
        array $input,
        string $expectedPath,
        string $expectedMessage
    ): void {
        try {
            UpdateRoomDetailsParam::createFromArray($input);
            $this->fail('Expected ValidationException was not thrown.');
        } catch (\DataType\Exception\ValidationException $exception) {
            $this->assertValidationProblems(
                $exception->getValidationProblems(),
                [$expectedPath => $expectedMessage]
            );
        }
    }
}
