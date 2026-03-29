<?php

declare(strict_types=1);

namespace BristolianTest\Parameters;

use Bristolian\Parameters\PropertyType\AnnotationText;
use Bristolian\Parameters\PropertyType\AnnotationTitle;
use Bristolian\Parameters\UpdateRoomAnnotationParam;
use BristolianTest\BaseTestCase;
use DataType\Messages;
use VarMap\ArrayVarMap;

/**
 * @coversNothing
 */
class UpdateRoomAnnotationParamTest extends BaseTestCase
{
    private static function validTitle(): string
    {
        return 'Annotation title meeting minimum length';
    }

    /**
     * @return \Generator<string, array{array<string, mixed>, string, string}>
     */
    public static function provides_valid_input_and_expected_output(): \Generator
    {
        $title = self::validTitle();

        yield 'title and text' => [
            ['title' => $title, 'text' => 'Body text'],
            $title,
            'Body text',
        ];
        yield 'empty text allowed' => [
            ['title' => $title, 'text' => ''],
            $title,
            '',
        ];
    }

    /**
     * @covers \Bristolian\Parameters\UpdateRoomAnnotationParam
     * @covers \Bristolian\Parameters\UpdateRoomAnnotationParam::__construct
     * @dataProvider provides_valid_input_and_expected_output
     * @param array<string, mixed> $input
     */
    public function test_createFromArray_parses_to_expected_values(
        array $input,
        string $expectedTitle,
        string $expectedText
    ): void {
        $params = UpdateRoomAnnotationParam::createFromArray($input);

        $this->assertSame($expectedTitle, $params->title);
        $this->assertSame($expectedText, $params->text);
    }

    /**
     * @covers \Bristolian\Parameters\UpdateRoomAnnotationParam
     * @dataProvider provides_valid_input_and_expected_output
     * @param array<string, mixed> $input
     */
    public function test_createFromVarMap_parses_to_expected_values(
        array $input,
        string $expectedTitle,
        string $expectedText
    ): void {
        $params = UpdateRoomAnnotationParam::createFromVarMap(new ArrayVarMap($input));

        $this->assertSame($expectedTitle, $params->title);
        $this->assertSame($expectedText, $params->text);
    }

    /**
     * @return \Generator<string, array{array<string, mixed>, string, string}>
     */
    public static function provides_invalid_input_and_expected_error(): \Generator
    {
        $title = self::validTitle();

        yield 'missing title' => [
            ['text' => 'Some body'],
            '/title',
            Messages::VALUE_NOT_SET,
        ];
        yield 'missing text' => [
            ['title' => $title],
            '/text',
            Messages::VALUE_NOT_SET,
        ];
        yield 'title too short' => [
            ['title' => str_repeat('x', AnnotationTitle::MINIMUM_LENGTH - 1), 'text' => 'ok'],
            '/title',
            Messages::STRING_TOO_SHORT,
        ];
        yield 'text too long' => [
            ['title' => $title, 'text' => str_repeat('b', AnnotationText::MAXIMUM_LENGTH + 1)],
            '/text',
            Messages::STRING_TOO_LONG,
        ];
    }

    /**
     * @covers \Bristolian\Parameters\UpdateRoomAnnotationParam
     * @dataProvider provides_invalid_input_and_expected_error
     * @param array<string, mixed> $input
     */
    public function test_createFromArray_rejects_invalid_input(
        array $input,
        string $expectedPath,
        string $expectedMessage
    ): void {
        try {
            UpdateRoomAnnotationParam::createFromArray($input);
            $this->fail('Expected ValidationException was not thrown.');
        } catch (\DataType\Exception\ValidationException $exception) {
            $this->assertValidationProblems(
                $exception->getValidationProblems(),
                [$expectedPath => $expectedMessage]
            );
        }
    }
}
