<?php

declare(strict_types=1);

namespace BristolianTest\Parameters\PropertyType;

use Bristolian\Parameters\PropertyType\RoomNoteMarkdown;
use BristolianTest\BaseTestCase;
use DataType\Create\CreateFromVarMap;
use DataType\DataType;
use DataType\GetInputTypesFromAttributes;
use DataType\Messages;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use VarMap\ArrayVarMap;

#[CoversClass(RoomNoteMarkdown::class)]
class RoomNoteMarkdownTest extends BaseTestCase
{
    /**
     * @return \Generator<string, array{array<string, mixed>, string|null}>
     */
    public static function provides_valid_input_and_expected_output(): \Generator
    {
        yield 'valid markdown' => [
            ['markdown_input' => 'Note body text'],
            'Note body text',
        ];
        yield 'empty allowed' => [['markdown_input' => ''], ''];
    }

    /**
     * @param array<string, mixed> $input
     */
    #[DataProvider('provides_valid_input_and_expected_output')]
    public function test_parses_valid_input_to_expected_output(array $input, ?string $expectedValue): void
    {
        $paramTest = RoomNoteMarkdownFixture::createFromVarMap(new ArrayVarMap($input));
        $this->assertSame($expectedValue, $paramTest->value);
    }

    public function test_rejects_missing_key(): void
    {
        try {
            RoomNoteMarkdownFixture::createFromVarMap(new ArrayVarMap([]));
            $this->fail('Expected ValidationException');
        }
        catch (\DataType\Exception\Runtime\ValidationException $validationException) {
            $this->assertValidationProblems(
                $validationException->getValidationProblems(),
                ['/markdown_input' => Messages::VALUE_NOT_SET]
            );
        }
    }

    public function test_rejects_too_long_markdown(): void
    {
        try {
            RoomNoteMarkdownFixture::createFromVarMap(new ArrayVarMap([
                'markdown_input' => str_repeat('m', RoomNoteMarkdown::MAXIMUM_LENGTH + 1),
            ]));
            $this->fail('Expected ValidationException');
        }
        catch (\DataType\Exception\Runtime\ValidationException $validationException) {
            $this->assertValidationProblems(
                $validationException->getValidationProblems(),
                ['/markdown_input' => Messages::STRING_TOO_LONG]
            );
        }
    }

    public function test_getInputType_returns_correct_name(): void
    {
        $propertyType = new RoomNoteMarkdown('note_markdown');
        $this->assertSame('note_markdown', $propertyType->getInputType()->getName());
    }
}

class RoomNoteMarkdownFixture implements DataType
{
    use CreateFromVarMap;
    use GetInputTypesFromAttributes;

    public function __construct(
        #[RoomNoteMarkdown('markdown_input')]
        public readonly ?string $value,
    ) {
    }
}
