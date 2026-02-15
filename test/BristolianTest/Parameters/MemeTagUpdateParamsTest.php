<?php

declare(strict_types = 1);

namespace BristolianTest\Parameters;

use Bristolian\Parameters\MemeTagUpdateParams;
use BristolianTest\BaseTestCase;
use DataType\Messages;
use VarMap\ArrayVarMap;

/**
 * @coversNothing
 */
class MemeTagUpdateParamsTest extends BaseTestCase
{
    /**
     * @return \Generator<string, array{array<string, mixed>, string, string, string}>
     */
    public static function provides_valid_input_and_expected_output(): \Generator
    {
        yield 'valid' => [
            ['meme_tag_id' => 'tag-123', 'type' => 'user', 'text' => 'valid1'],
            'tag-123', 'user', 'valid1',
        ];
        yield 'tag at min length' => [
            ['meme_tag_id' => 'id', 'type' => 't', 'text' => '1234'],
            'id', 't', '1234',
        ];
    }

    /**
     * @covers \Bristolian\Parameters\MemeTagUpdateParams
     * @covers \Bristolian\Parameters\PropertyType\BasicString
     * @covers \Bristolian\Parameters\PropertyType\TagString
     * @dataProvider provides_valid_input_and_expected_output
     * @param array<string, mixed> $input
     */
    public function test_parses_valid_input_to_expected_output(
        array $input,
        string $expectedMemeTagId,
        string $expectedType,
        string $expectedText
    ): void {
        $params = MemeTagUpdateParams::createFromVarMap(new ArrayVarMap($input));

        $this->assertSame($expectedMemeTagId, $params->meme_tag_id);
        $this->assertSame($expectedType, $params->type);
        $this->assertSame($expectedText, $params->text);
    }

    /**
     * @return \Generator<string, array{array<string, mixed>, string, string}>
     */
    public static function provides_invalid_input_and_expected_error(): \Generator
    {
        yield 'missing meme_tag_id' => [
            ['type' => 'user', 'text' => 'valid1'],
            '/meme_tag_id',
            Messages::VALUE_NOT_SET,
        ];
        yield 'missing type' => [
            ['meme_tag_id' => 'id', 'text' => 'valid1'],
            '/type',
            Messages::VALUE_NOT_SET,
        ];
        yield 'missing text' => [
            ['meme_tag_id' => 'id', 'type' => 'user'],
            '/text',
            Messages::VALUE_NOT_SET,
        ];
        yield 'text too short' => [
            ['meme_tag_id' => 'id', 'type' => 'user', 'text' => '123'],
            '/text',
            Messages::STRING_TOO_SHORT,
        ];
    }

    /**
     * @covers \Bristolian\Parameters\MemeTagUpdateParams
     * @dataProvider provides_invalid_input_and_expected_error
     * @param array<string, mixed> $input
     */
    public function test_rejects_invalid_input_with_expected_error(
        array $input,
        string $errorPath,
        string $expectedErrorMessage
    ): void {
        try {
            MemeTagUpdateParams::createFromVarMap(new ArrayVarMap($input));
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
