<?php

declare(strict_types = 1);

namespace BristolianTest\Parameters;

use Bristolian\Parameters\UserProfileUpdateParams;
use BristolianTest\BaseTestCase;
use DataType\Messages;
use VarMap\ArrayVarMap;

/**
 * @coversNothing
 */
class UserProfileUpdateParamsTest extends BaseTestCase
{
    /**
     * @return \Generator<string, array{array<string, mixed>, string, string}>
     */
    public static function provides_valid_input_and_expected_output(): \Generator
    {
        yield 'valid' => [
            ['display_name' => 'ValidName', 'about_me' => 'About me text here.'],
            'ValidName', 'About me text here.',
        ];
        yield 'min display name' => [
            ['display_name' => 'abcd', 'about_me' => ''],
            'abcd', '',
        ];
    }

    /**
     * @covers \Bristolian\Parameters\UserProfileUpdateParams
     * @covers \Bristolian\Parameters\PropertyType\DisplayName
     * @covers \Bristolian\Parameters\PropertyType\AboutMeText
     * @dataProvider provides_valid_input_and_expected_output
     * @param array<string, mixed> $input
     */
    public function test_parses_valid_input_to_expected_output(
        array $input,
        string $expectedDisplayName,
        string $expectedAboutMe
    ): void {
        $params = UserProfileUpdateParams::createFromVarMap(new ArrayVarMap($input));

        $this->assertSame($expectedDisplayName, $params->display_name);
        $this->assertSame($expectedAboutMe, $params->about_me);
    }

    /**
     * @return \Generator<string, array{array<string, mixed>, string, string}>
     */
    public static function provides_invalid_input_and_expected_error(): \Generator
    {
        yield 'missing display_name' => [
            ['about_me' => 'About me'],
            '/display_name',
            Messages::VALUE_NOT_SET,
        ];
        yield 'missing about_me' => [
            ['display_name' => 'ValidName'],
            '/about_me',
            Messages::VALUE_NOT_SET,
        ];
        yield 'display_name too short' => [
            ['display_name' => 'abc', 'about_me' => 'About me'],
            '/display_name',
            Messages::STRING_TOO_SHORT,
        ];
        yield 'about_me too long' => [
            ['display_name' => 'ValidName', 'about_me' => str_repeat('a', 4097)],
            '/about_me',
            Messages::STRING_TOO_LONG,
        ];
    }

    /**
     * @covers \Bristolian\Parameters\UserProfileUpdateParams
     * @dataProvider provides_invalid_input_and_expected_error
     * @param array<string, mixed> $input
     */
    public function test_rejects_invalid_input_with_expected_error(
        array $input,
        string $errorPath,
        string $expectedErrorMessage
    ): void {
        try {
            UserProfileUpdateParams::createFromVarMap(new ArrayVarMap($input));
            $this->fail("Expected ValidationException was not thrown.");
        }
        catch (\DataType\Exception\ValidationException $ve) {
            $this->assertValidationProblems(
                $ve->getValidationProblems(),
                [$errorPath => $expectedErrorMessage]
            );
        }
    }
}
