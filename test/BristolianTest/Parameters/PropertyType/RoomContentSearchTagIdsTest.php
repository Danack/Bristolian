<?php

declare(strict_types=1);

namespace BristolianTest\Parameters\PropertyType;

use Bristolian\Parameters\ProcessRule\OptionalStringToRoomSearchTagIds;
use Bristolian\Parameters\PropertyType\RoomContentSearchTagIds;
use BristolianTest\BaseTestCase;
use DataType\Create\CreateFromVarMap;
use DataType\DataType;
use DataType\GetInputTypesFromAttributes;
use VarMap\ArrayVarMap;

/**
 * @covers \Bristolian\Parameters\PropertyType\RoomContentSearchTagIds
 * @covers \Bristolian\Parameters\ProcessRule\OptionalStringToRoomSearchTagIds
 */
class RoomContentSearchTagIdsTest extends BaseTestCase
{
    /**
     * @return \Generator<string, array{array<string, mixed>, string[]}>
     */
    public static function provides_valid_input_and_expected_output(): \Generator
    {
        yield 'missing' => [[], []];
        yield 'empty string' => [['tag_ids_input' => ''], []];
        yield 'single tag' => [['tag_ids_input' => 'tag1'], ['tag1']];
        yield 'multiple tags' => [['tag_ids_input' => 'id1,id2,id3'], ['id1', 'id2', 'id3']];
        yield 'trimmed' => [['tag_ids_input' => '  a  ,  b  ,  c  '], ['a', 'b', 'c']];
        yield 'max five' => [['tag_ids_input' => 'a,b,c,d,e'], ['a', 'b', 'c', 'd', 'e']];
    }

    /**
     * @dataProvider provides_valid_input_and_expected_output
     * @param array<string, mixed> $input
     * @param string[] $expected
     */
    public function test_parses_valid_input_to_expected_output(array $input, array $expected): void
    {
        $paramTest = RoomContentSearchTagIdsFixture::createFromVarMap(new ArrayVarMap($input));
        $this->assertSame($expected, $paramTest->value);
    }

    /**
     * @return \Generator<string, array{array<string, mixed>, string}>
     */
    public static function provides_invalid_input_and_expected_error(): \Generator
    {
        yield 'empty tag in list' => [
            ['tag_ids_input' => 'id1,,id2'],
            OptionalStringToRoomSearchTagIds::ERROR_EMPTY_TAG,
        ];
        yield 'only comma' => [
            ['tag_ids_input' => ','],
            OptionalStringToRoomSearchTagIds::ERROR_EMPTY_TAG,
        ];
        yield 'leading comma' => [
            ['tag_ids_input' => ',id1'],
            OptionalStringToRoomSearchTagIds::ERROR_EMPTY_TAG,
        ];
        yield 'trailing comma' => [
            ['tag_ids_input' => 'id1,'],
            OptionalStringToRoomSearchTagIds::ERROR_EMPTY_TAG,
        ];
        yield 'too many tags' => [
            ['tag_ids_input' => 'a,b,c,d,e,f'],
            OptionalStringToRoomSearchTagIds::ERROR_TOO_MANY_TAGS,
        ];
    }

    /**
     * @dataProvider provides_invalid_input_and_expected_error
     * @param array<string, mixed> $input
     */
    public function test_rejects_invalid_input_with_expected_error(array $input, string $expectedErrorMessage): void
    {
        try {
            RoomContentSearchTagIdsFixture::createFromVarMap(new ArrayVarMap($input));
            $this->fail('Expected ValidationException was not thrown.');
        } catch (\DataType\Exception\ValidationException $ve) {
            $this->assertValidationProblems(
                $ve->getValidationProblems(),
                ['/tag_ids_input' => $expectedErrorMessage]
            );
        }
    }

    public function test_getInputType_returns_correct_name(): void
    {
        $propertyType = new RoomContentSearchTagIds('tag_ids');
        $this->assertSame('tag_ids', $propertyType->getInputType()->getName());
    }

    /**
     * @covers \Bristolian\Parameters\ProcessRule\OptionalStringToRoomSearchTagIds::updateParamDescription
     */
    public function test_process_rule_updateParamDescription_sets_type_and_description(): void
    {
        $rule = new OptionalStringToRoomSearchTagIds();
        $paramDescription = new \DataType\OpenApi\OpenApiV300ParamDescription('tag_ids');

        $rule->updateParamDescription($paramDescription);

        $this->assertSame('array', $paramDescription->getType());
        $this->assertSame('Comma-separated tag ids, max 5', $paramDescription->getDescription());
    }
}

class RoomContentSearchTagIdsFixture implements DataType
{
    use CreateFromVarMap;
    use GetInputTypesFromAttributes;

    /**
     * @param string[] $value
     */
    public function __construct(
        #[RoomContentSearchTagIds('tag_ids_input')]
        public readonly array $value,
    ) {
    }
}
