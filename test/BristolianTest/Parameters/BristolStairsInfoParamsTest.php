<?php

declare(strict_types = 1);

namespace BristolianTest\Parameters;

use BristolianTest\BaseTestCase;
use Bristolian\Parameters\BristolStairsInfoParams;
use DataType\Messages;
use VarMap\ArrayVarMap;

/**
 * @coversNothing
 */
class BristolStairsInfoParamsTest extends BaseTestCase
{
    /**
     * @return \Generator<string, array{array<string, mixed>, string, string, string}>
     */
    public static function provides_valid_input_and_expected_output(): \Generator
    {
        yield 'valid' => [
            [
                'bristol_stair_info_id' => 'stairs_123',
                'description' => 'A nice set of stairs',
                'steps' => '25',
            ],
            'stairs_123',
            'A nice set of stairs',
            '25',
        ];
    }

    /**
     * @covers \Bristolian\Parameters\BristolStairsInfoParams
     * @dataProvider provides_valid_input_and_expected_output
     * @param array<string, mixed> $input
     */
    public function test_parses_valid_input_to_expected_output(
        array $input,
        string $expectedId,
        string $expectedDescription,
        string $expectedSteps
    ): void {
        $params = BristolStairsInfoParams::createFromVarMap(new ArrayVarMap($input));
        $this->assertSame($expectedId, $params->bristol_stair_info_id);
        $this->assertSame($expectedDescription, $params->description);
        $this->assertSame($expectedSteps, $params->steps);
    }

    /**
     * @return \Generator<string, array{array<string, mixed>, array<string, string>}>
     */
    public static function provides_invalid_input_and_expected_errors(): \Generator
    {
        yield 'missing bristol_stair_info_id' => [
            ['description' => 'A nice set of stairs', 'steps' => '25'],
            ['/bristol_stair_info_id' => Messages::VALUE_NOT_SET],
        ];
        yield 'missing description' => [
            ['bristol_stair_info_id' => 'stairs_123', 'steps' => '25'],
            ['/description' => Messages::VALUE_NOT_SET],
        ];
        yield 'missing steps' => [
            ['bristol_stair_info_id' => 'stairs_123', 'description' => 'A nice set of stairs'],
            ['/steps' => Messages::VALUE_NOT_SET],
        ];
        yield 'invalid types' => [
            ['bristol_stair_info_id' => 123, 'description' => 456, 'steps' => 789],
            [
                '/bristol_stair_info_id' => Messages::STRING_EXPECTED,
                '/description' => Messages::STRING_EXPECTED,
            ],
        ];
    }

    /**
     * @covers \Bristolian\Parameters\BristolStairsInfoParams
     * @dataProvider provides_invalid_input_and_expected_errors
     * @param array<string, mixed> $input
     * @param array<string, string> $expectedProblems
     */
    public function test_rejects_invalid_input_with_expected_errors(array $input, array $expectedProblems): void
    {
        try {
            BristolStairsInfoParams::createFromVarMap(new ArrayVarMap($input));
            $this->fail("Expected ValidationException was not thrown.");
        }
        catch (\DataType\Exception\ValidationException $ve) {
            $this->assertValidationProblems($ve->getValidationProblems(), $expectedProblems);
        }
    }
}
