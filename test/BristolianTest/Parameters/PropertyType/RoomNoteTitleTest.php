<?php

declare(strict_types=1);

namespace BristolianTest\Parameters\PropertyType;

use Bristolian\Parameters\PropertyType\RoomNoteTitle;
use BristolianTest\BaseTestCase;
use DataType\Create\CreateFromVarMap;
use DataType\DataType;
use DataType\GetInputTypesFromAttributes;
use DataType\Messages;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use VarMap\ArrayVarMap;

#[CoversClass(RoomNoteTitle::class)]
class RoomNoteTitleTest extends BaseTestCase
{
    /**
     * @return \Generator<string, array{array<string, mixed>, string|null}>
     */
    public static function provides_valid_input_and_expected_output(): \Generator
    {
        yield 'valid title' => [
            ['title_input' => 'Meeting notes'],
            'Meeting notes',
        ];
        yield 'trimmed' => [
            ['title_input' => '  trimmed title  '],
            'trimmed title',
        ];
    }

    /**
     * @param array<string, mixed> $input
     */
    #[DataProvider('provides_valid_input_and_expected_output')]
    public function test_parses_valid_input_to_expected_output(array $input, ?string $expectedValue): void
    {
        $paramTest = RoomNoteTitleFixture::createFromVarMap(new ArrayVarMap($input));
        $this->assertSame($expectedValue, $paramTest->value);
    }

    /**
     * @return \Generator<string, array{array<string, mixed>, string}>
     */
    public static function provides_invalid_input_and_expected_error(): \Generator
    {
        yield 'missing' => [[], Messages::VALUE_NOT_SET];
        yield 'empty string' => [['title_input' => ''], Messages::STRING_TOO_SHORT];
        yield 'whitespace only' => [['title_input' => '   '], Messages::STRING_TOO_SHORT];
    }

    /**
     * @param array<string, mixed> $input
     */
    #[DataProvider('provides_invalid_input_and_expected_error')]
    public function test_rejects_invalid_input_with_expected_error(array $input, string $expectedErrorMessage): void
    {
        try {
            RoomNoteTitleFixture::createFromVarMap(new ArrayVarMap($input));
            $this->fail('Expected ValidationException');
        }
        catch (\DataType\Exception\Runtime\ValidationException $validationException) {
            $this->assertValidationProblems(
                $validationException->getValidationProblems(),
                ['/title_input' => $expectedErrorMessage]
            );
        }
    }

    public function test_rejects_too_long_title(): void
    {
        try {
            RoomNoteTitleFixture::createFromVarMap(new ArrayVarMap([
                'title_input' => str_repeat('t', RoomNoteTitle::MAXIMUM_LENGTH + 1),
            ]));
            $this->fail('Expected ValidationException');
        }
        catch (\DataType\Exception\Runtime\ValidationException $validationException) {
            $this->assertValidationProblems(
                $validationException->getValidationProblems(),
                ['/title_input' => Messages::STRING_TOO_LONG]
            );
        }
    }

    public function test_getInputType_returns_correct_name(): void
    {
        $propertyType = new RoomNoteTitle('note_title');
        $this->assertSame('note_title', $propertyType->getInputType()->getName());
    }
}

class RoomNoteTitleFixture implements DataType
{
    use CreateFromVarMap;
    use GetInputTypesFromAttributes;

    public function __construct(
        #[RoomNoteTitle('title_input')]
        public readonly ?string $value,
    ) {
    }
}
