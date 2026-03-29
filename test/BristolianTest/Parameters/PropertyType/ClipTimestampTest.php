<?php

declare(strict_types=1);

namespace BristolianTest\Parameters\PropertyType;

use Bristolian\Parameters\ProcessRule\ClipEndTimeAfterStartTime;
use Bristolian\Parameters\ProcessRule\ParseClipTimestampToSeconds;
use Bristolian\Parameters\PropertyType\ClipTimestamp;
use BristolianTest\BaseTestCase;
use DataType\Create\CreateFromVarMap;
use DataType\DataType;
use DataType\GetInputTypesFromAttributes;
use DataType\Messages;
use VarMap\ArrayVarMap;

/**
 * @coversNothing
 */
class ClipTimestampTest extends BaseTestCase
{
    /**
     * @return \Generator<string, array{array<string, mixed>, int}>
     */
    public static function provides_valid_standalone_input_and_expected_seconds(): \Generator
    {
        yield 'plain seconds' => [['ts' => '0'], 0];
        yield 'minutes and seconds' => [['ts' => '1:15'], 75];
        yield 'hms' => [['ts' => '0:4:15'], 255];
    }

    /**
     * @covers \Bristolian\Parameters\PropertyType\ClipTimestamp::__construct
     * @covers \Bristolian\Parameters\PropertyType\ClipTimestamp::getInputType
     * @dataProvider provides_valid_standalone_input_and_expected_seconds
     * @param array<string, mixed> $input
     */
    public function test_standalone_parses_valid_input_to_expected_seconds(array $input, int $expectedSeconds): void
    {
        $fixture = ClipTimestampStandaloneFixture::createFromVarMap(new ArrayVarMap($input));
        $this->assertSame($expectedSeconds, $fixture->seconds);
    }

    /**
     * @return \Generator<string, array{array<string, mixed>, string}>
     */
    public static function provides_invalid_standalone_input_and_expected_error(): \Generator
    {
        yield 'missing' => [[], Messages::VALUE_NOT_SET];
        yield 'empty string' => [['ts' => ''], Messages::STRING_TOO_SHORT];
        yield 'invalid timestamp' => [['ts' => 'not-a-time'], ParseClipTimestampToSeconds::ERROR_INVALID_TIMESTAMP];
        yield 'too long string' => [['ts' => str_repeat('9', 33)], Messages::STRING_TOO_LONG];
    }

    /**
     * @covers \Bristolian\Parameters\PropertyType\ClipTimestamp::getInputType
     * @dataProvider provides_invalid_standalone_input_and_expected_error
     * @param array<string, mixed> $input
     */
    public function test_standalone_rejects_invalid_input_with_expected_error(array $input, string $expectedErrorMessage): void
    {
        try {
            ClipTimestampStandaloneFixture::createFromVarMap(new ArrayVarMap($input));
            $this->fail('Expected ValidationException was not thrown.');
        } catch (\DataType\Exception\ValidationException $exception) {
            $this->assertValidationProblems(
                $exception->getValidationProblems(),
                ['/ts' => $expectedErrorMessage]
            );
        }
    }

    /**
     * @covers \Bristolian\Parameters\PropertyType\ClipTimestamp::getInputType
     */
    public function test_pair_parses_when_end_is_after_start(): void
    {
        $fixture = ClipTimestampPairFixture::createFromVarMap(new ArrayVarMap([
            'start_time' => '1:00',
            'end_time' => '2:00',
        ]));
        $this->assertSame(60, $fixture->start_seconds);
        $this->assertSame(120, $fixture->end_seconds);
    }

    /**
     * @covers \Bristolian\Parameters\PropertyType\ClipTimestamp::getInputType
     */
    public function test_pair_rejects_end_not_after_start(): void
    {
        try {
            ClipTimestampPairFixture::createFromVarMap(new ArrayVarMap([
                'start_time' => '4:15',
                'end_time' => '1:15',
            ]));
            $this->fail('Expected ValidationException was not thrown.');
        } catch (\DataType\Exception\ValidationException $exception) {
            $this->assertValidationProblems(
                $exception->getValidationProblems(),
                ['/end_time' => ClipEndTimeAfterStartTime::ERROR_END_NOT_AFTER_START]
            );
        }
    }

    /**
     * @covers \Bristolian\Parameters\PropertyType\ClipTimestamp::getInputType
     */
    public function test_getInputType_returns_correct_name_without_start_reference(): void
    {
        $propertyType = new ClipTimestamp('start_time');
        $this->assertSame('start_time', $propertyType->getInputType()->getName());
    }

    /**
     * @covers \Bristolian\Parameters\PropertyType\ClipTimestamp::getInputType
     */
    public function test_getInputType_returns_correct_name_with_start_reference(): void
    {
        $propertyType = new ClipTimestamp('end_time', 'start_time');
        $this->assertSame('end_time', $propertyType->getInputType()->getName());
    }
}

class ClipTimestampStandaloneFixture implements DataType
{
    use CreateFromVarMap;
    use GetInputTypesFromAttributes;

    public function __construct(
        #[ClipTimestamp('ts')]
        public readonly int $seconds,
    ) {
    }
}

class ClipTimestampPairFixture implements DataType
{
    use CreateFromVarMap;
    use GetInputTypesFromAttributes;

    public function __construct(
        #[ClipTimestamp('start_time')]
        public readonly int $start_seconds,
        #[ClipTimestamp('end_time', 'start_time')]
        public readonly int $end_seconds,
    ) {
    }
}
