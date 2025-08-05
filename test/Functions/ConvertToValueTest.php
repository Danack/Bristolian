<?php

namespace Functions;

use BristolianTest\BaseTestCase;
use Laminas\Diactoros\ServerRequest;
use Laminas\Diactoros\Response;
use Bristolian\DataType\LinkParam;
use VarMap\ArrayVarMap;
use DataType\Exception\ValidationException;
use function convertToValue;


/**
 * @coversNothing
 * @group wip
 */
class ConvertToValueTest extends BaseTestCase
{

    public function provides_convertToValue_works()
    {
        yield [123, 123];

        yield [
            new \DateTime("2025-08-04 10:00:00"),
            '2025-08-04T10:00:00+00:00'
        ];

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



    public function provides_convertToValue_fails()
    {
        yield [new \StdClass, "Unsupported type [%s] of class [%s] for toArray."];

        yield [fopen("php://memory", "rb"), "Unsupported type [%s] for toArray."];
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
}
