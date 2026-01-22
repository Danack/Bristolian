<?php

declare(strict_types = 1);

namespace BristolianTest;

use Bristolian\Exception\BristolianException;
use BristolianTest\TestFixtures\ToArrayClass;
use DataType\DataStorage\TestArrayDataStorage;
use SlimDispatcher\Response\JsonResponse;

/**
 * @coversNothing
 */
class FunctionsTest extends BaseTestCase
{
    /**
     * @covers ::getRandomId
     */
    public function testGetRandomId()
    {
        $id = getRandomId();
        $this->assertSame(64, strlen($id));
    }


    /**
     * @covers ::formatLinesWithCount
     */
    public function test_formatLinesWithCount()
    {
        $result = formatLinesWithCount(['foo', 'bar']);
        $expected = <<<TEXT
#0 foo
#1 bar

TEXT;
        $this->assertSame($expected, $result);
    }

    public function test_continuallyExecuteCallable()
    {
    }

    /**
     * @covers json_decode_safe
     */
    public function test_json_decode_safe()
    {
        $data = ['foo' => 'bar'];
        $output = json_decode_safe(json_encode($data));

        $this->assertSame($data, $output);
    }

    public function test_json_encode_safe()
    {
    }

    public function test_getExceptionInfoAsArray()
    {
    }

    /**
     * @covers ::peak_memory
     */
    public function test_peak_memory()
    {
        $memory_string = peak_memory();
        $this->assertGreaterThanOrEqual(
            9,
            strlen($memory_string),
            "memory used is only " . $memory_string
        );
    }


    public function test_convertToValue()
    {
    }


    public function test_fetchUri()
    {
    }


    public function test_convertStringToHtmlResponse()
    {
    }


    public function test_getMask()
    {
    }


    public function test_twiddleWithShit()
    {
    }


    public function test_showTotalErrorPage()
    {
    }


    public function test_getMemoryLimit()
    {
    }

    /**
     * @covers ::getPercentMemoryUsed
     */
    public function test_getPercentMemoryUsed()
    {
        $memoryLimit = ini_get('memory_limit');
        if ($memoryLimit === "-1") {
            $this->markTestSkipped("No memory limit, cannot test getPercentMemoryUsed");
        }

        [$percentMemoryUsed, $memoryLimitValue] = getPercentMemoryUsed();
        $this->assertGreaterThanOrEqual(0, $percentMemoryUsed);
        $this->assertLessThanOrEqual(100, $percentMemoryUsed);
    }

    /**
     * @covers ::createErrorJsonResponse
     */
    public function test_createErrorJsonResponse()
    {
        $result = createErrorJsonResponse([]);
        $this->assertNull($result, "No errors should have returned null.");


        $dataStorage = TestArrayDataStorage::fromSingleValueAndSetCurrentPosition(
            'foo',
            'bar'
        );

        $validation_problem = new \DataType\ValidationProblem($dataStorage, "Test error");

        $response = createErrorJsonResponse([$validation_problem]);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertSame(400, $response->getStatus());
    }


    /**
     * @covers ::remove_install_prefix_from_path
     */
    public function test_normaliseFilePath()
    {
        $result = remove_install_prefix_from_path("/var/app/test");
        $this->assertSame('test', $result);
    }


    /**
     * @covers ::getEnvString
     */
    public function test_getEnvString()
    {
        putenv("FOO=BAR");

        $result = getEnvString("FOO");
        $this->assertSame("BAR", $result);

        $this->expectException(\Bristolian\Exception\BristolianException::class);
        getEnvString("NONEXISTENT");
    }

    /**
     * @covers ::array_contains
     */
    public function test_array_contains()
    {
        $this->assertFalse(array_contains(true, [1]));
        $this->assertTrue(array_contains(1, [1]));
    }

    public function test_generate_password_hash()
    {
        $result = generate_password_hash("Hello");
        $this->assertEquals(60, strlen($result));
    }

    /**
     * @covers ::get_password_options
     */
    public function test_get_password_options()
    {
        $options = get_password_options();
        $this->assertArrayHasKey("cost", $options);
        $this->assertGreaterThanOrEqual(12, $options["cost"]);
    }

    public static function provides_slugify()
    {
        yield ['Cómo hablar en sílabas', 'como-hablar-en-silabas'];
        yield ['Álix Ãxel', 'alix-axel'];
        yield ['Álix----_Ãxel!?!?', 'alix-axel'];
        yield [
            'FOIA Section 12 and the 18-hour time/cost limit',
            'foia-section-12-and-the-18-hour-time-cost-limit'
        ];

        yield [
            'co-ordinate',
            'co-ordinate'
        ];
    }

    /**
     * @dataProvider provides_slugify
     * @covers slugify
     * @param string $input
     * @param string $expected
     * @return void
     */
    public function test_slugify(string $input, string $expected)
    {
        $result = slugify($input);
        $this->assertSame($expected, $result);
    }





    public static function provides_sanitise_filename()
    {
        yield ['John..Anyman', 'john_anyman'];
        yield ['John/Anyman', 'john_anyman'];
        yield ['John\\Anyman', 'john_anyman'];
    }

    /**
     * @covers ::sanitise_filename
     * @dataProvider provides_sanitise_filename
     * @param string $input
     * @param string $expected
     * @return void
     */
    public function test_sanitise_filename(string $input, string $expected)
    {
        $result = sanitise_filename($input);
        $this->assertSame($expected, $result);
    }

    public static function provides_standardise_username_to_filename()
    {
        yield ['John Anyman', 'john_anyman'];
        yield ['John..Anyman', 'john_anyman'];
        yield ['John/Anyman', 'john_anyman'];
        yield ['John\\Anyman', 'john_anyman'];
    }

    /**
     * @covers ::standardise_username_to_filename
     * @dataProvider provides_standardise_username_to_filename
     * @param string $input
     * @param string $expected
     * @return void
     */
    public function test_standardise_username_to_filename(string $input, string $expected)
    {
        $result = standardise_username_to_filename($input);
        $this->assertSame($expected, $result);
    }

    public static function provides_escapeMySqlLikeString()
    {
        yield ['foo\\bar', 'foo\\\\bar'];
        yield ['foo_bar', 'foo\\_bar'];
        yield ['foo%bar', 'foo\\%bar'];
    }

    /**
     * @dataProvider provides_escapeMySqlLikeString
     * @covers ::escapeMySqlLikeString
     */
    public function test_escapeMySqlLikeString(string $input, string $expected)
    {
        $result = escapeMySqlLikeString($input);
        $this->assertSame($expected, $result);
    }


    /**
     * @covers ::get_external_source_link
     */
    public function test_get_external_source_link()
    {
        // Case without /raw/
        $result = get_external_source_link('www.google.com');
        $this->assertSame("External source is: www.google.com", $result);

        // Case with /raw/
        $result = get_external_source_link('https://gist.githubusercontent.com/Danack/89e8d9b25dac35e1a68cd3b576a17a36/raw/fb924a43a241d151ba5e659e21a272647658d4e7/words.md');

        $expected = "External source is: <a href='https://gist.githubusercontent.com/Danack/89e8d9b25dac35e1a68cd3b576a17a36'>https://gist.githubusercontent.com/Danack/89e8d9b25dac35e1a68cd3b576a17a36/raw/fb924a43a241d151ba5e659e21a272647658d4e7/words.md</a>";

        $this->assertSame($expected, $result);
    }


    public function test_render_markdown_file()
    {
        $this->markTestSkipped('Test not implemented yet');

        $document = new \deadish\UserDocument(
            \Bristolian\Types\DocumentType::markdown_file->value,
            "Some title",
            "FOIA Section 12 and the 18-hour time_cost limit.md"
        );
        $user = new \User(\Bristolian\Types\UserList::sid->value);

        $document->setUser($user);

        $result = render_markdown_file($document);
        $this->assertStringStartsWith(
            '<h1>FOIA Section 12 and the 18-hour time/cost limit<a id="user-content-foia-section-12-and-the-18-hour-timecost-limit" href="#content-foia-section-12-and-the-18-hour-timecost-limit" class="h',
            $result
        );
    }

    /**
     * @covers ::convertToArrayOfObjects
     */
    public function test_convertToArrayOfObjects()
    {
        $int_value = 1234;
        $data = [[
            'test_string' => 'foobar',
            'test_int' => $int_value
        ]];

        $objects = convertToArrayOfObjects(PdoSimple\PdoSimpleTestObject::class, $data);
        $this->assertCount(1, $objects);

        $object = $objects[0];
        $this->assertInstanceOf(PdoSimple\PdoSimpleTestObject::class, $object);
        $this->assertSame('foobar', $object->test_string);
        $this->assertSame($int_value, $object->test_int);

        $this->expectException(\Bristolian\Exception\BristolianException::class);
        $this->expectExceptionMessage(\Bristolian\Exception\BristolianException::CANNOT_INSTANTIATE);

        convertToArrayOfObjects(\StdClass::class, []);
    }

    public static function provides_normalize_file_extension_works()
    {
        yield ["sample.pdf", ['pdf']];
        yield ["sample.pdf", ['pdf', 'txt', 'jpg']];
    }

    /**
     * @covers ::normalize_file_extension
     * @dataProvider provides_normalize_file_extension_works
     * @param string $original_filename
     * @param string[] $allowed_extensions
     * @throws BristolianException
     */
    public function test_normalize_file_extension_works(string $original_filename, array $allowed_extensions)
    {
        $result = normalize_file_extension(
            $original_filename,
            "Currently unused",
            $allowed_extensions
        );

        $this->assertSame('pdf', $result);
    }

    /**
     * @covers ::normalize_file_extension
     */
    public function test_normalize_file_extension_throws()
    {
        $this->expectException(\Bristolian\Exception\BristolianException::class);
        normalize_file_extension(
            "sample.pdf",
            "Currently unused",
            ['PDF'] // incorrect upper case
        );
    }

    public static function provides_normalize_file_extension_null()
    {
        // file type is not allowed
        yield ["sample.pdf", ['txt']];
        // file name has no extension
        yield ["pdf", ['pdf']];
    }

    /**
     * @covers ::normalize_file_extension
     * @dataProvider provides_normalize_file_extension_null
     * @param string $original_filename
     * @param string[] $allowed_extensions
     * @throws BristolianException
     */
    public function test_normalize_file_extension_null(string $original_filename, $allowed_extensions)
    {
        $result = normalize_file_extension(
            $original_filename,
            "Currently unused",
            $allowed_extensions
        );

        $this->assertNull($result);
    }



    public static function provides_convertToValue_works()
    {
        yield ['foo', 'foo'];
        yield [null, null];
        yield [['foo' => 'bar'], ['foo' => 'bar']];

        $string_value = "John";
        $int_value = 1234;
        $toArrayClass = new ToArrayClass($string_value, $int_value);

        yield [$toArrayClass, ['foo' => $string_value, 'bar' => $int_value]];
    }

    /**
     * @covers ::convertToValue
     * @dataProvider provides_convertToValue_works
     */
    public function test_convertToValue_works(mixed $input, mixed $expected_value)
    {
        [$error, $value] = convertToValue($input);

        $this->assertNull($error);
        $this->assertSame($expected_value, $value);
    }

    public static function provides_get_readable_variable_type_works()
    {
        yield ['some string', "a string"];
        yield [5, "an int"];
        yield [new \StdClass, "an object of type [stdClass]"];
    }

    /**
     * @dataProvider provides_get_readable_variable_type_works
     */
    public function test_get_readable_variable_type(mixed $value, string $expected_message)
    {
        $result = get_readable_variable_type($value);

        $this->assertStringContainsString(
            $expected_message,
            $result
        );
    }

    /**
     * @covers ::customSort
     */
    public function test_customSort()
    {
        $input = ['name', 'id', 'user_id', 'created_at', 'modified_at', 'group_id', 'email'];
        $sorted = customSort($input);

        $expected = ['id', 'group_id', 'user_id', 'email', 'name',  'created_at', 'modified_at'];
        $this->assertSame($expected, $sorted);
    }

    /**
     * @covers ::get_supported_room_file_extensions
     * @covers ::get_supported_room_file_extensions
     */
    public function testMimeTypesAreAdequate()
    {
        $file_room_extensions = get_supported_room_file_extensions();
        foreach ($file_room_extensions as $extension) {
            $mimetype = get_mime_type_from_extension($extension);
            $this->assertNotNull($mimetype, "Mimetype for extension $extension is null");
        }

        $meme_extensions = get_supported_meme_file_extensions();
        foreach ($meme_extensions as $extension) {
            $mimetype = get_mime_type_from_extension($extension);
            $this->assertNotNull($mimetype, "Mimetype for extension $extension is null");
        }
    }
}
