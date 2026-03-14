<?php

namespace Functions;

use BristolianTest\BaseTestCase;
use Bristolian\Repo\MemeStorageRepo\MemeFileState;
use Laminas\Diactoros\ServerRequest;
use Laminas\Diactoros\Response;
use Bristolian\Parameters\LinkParam;
use VarMap\ArrayVarMap;
use DataType\Exception\ValidationException;
use function convertToValue;
use function convertToValueSafe;

/**
 * @coversNothing
 */
class ConvertToValueTest extends BaseTestCase
{

    public static function provides_convertToValue_works()
    {
        yield [123, 123];

        yield [
            new \DateTime("2025-08-04 10:00:00"),
            '2025-08-04T10:00:00+00:00'
        ];

        yield [MemeFileState::INITIAL, 'initial'];
    }




    /**
     * @covers ::convertToValue
     * @dataProvider provides_convertToValue_works
     */
    public function test_convertToValue_works(mixed $input, mixed $expected_value)
    {
        [$error, $value] = convertToValue($input);

        $this->assertNull($error);
        $this->assertEquals($expected_value, $value);
    }



    public static function provides_convertToValue_fails()
    {
        yield [new \StdClass, "Unsupported type [%s] of class [%s] for toArray."];

        yield [fopen("php://memory", "rb"), "Unsupported type [%s] for toArray."];

        yield [[new \StdClass], "Unsupported type [%s] of class [%s] for toArray."];
    }


    /**
     * @covers ::convertToValue
     * @dataProvider provides_convertToValue_fails
     */
    public function test_convertToValue_fails(mixed $input, string $expected_error)
    {
        [$error, $value] = convertToValue($input);

        $this->assertNull($value);
        $this->assertStringMatchesTemplateString(
            strtolower($expected_error),
            strtolower($error)
        );
    }

    /**
     * @covers ::convertToValueSafe
     */
    public function test_convertToValueSafe_returns_value_when_conversion_succeeds(): void
    {
        $result = convertToValueSafe(42);
        $this->assertSame(42, $result);

        $result = convertToValueSafe(['a' => 1, 'b' => 2]);
        $this->assertSame(['a' => 1, 'b' => 2], $result);
    }
}
