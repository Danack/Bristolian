<?php

declare(strict_types = 1);

namespace BristolianTest;

use Bristolian\Exception\BristolianException;
use Bristolian\Types\DocumentType;
use BristolianTest\TestFixtures\ToArrayClass;
use DataType\DataStorage\TestArrayDataStorage;
use League\Flysystem\Filesystem;
use League\Flysystem\Local\LocalFilesystemAdapter;
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
     * @covers ::generateSecureToken
     */
    public function test_generateSecureToken_returns_url_safe_token_of_expected_length(): void
    {
        $token = generateSecureToken();
        $this->assertSame(43, strlen($token), 'base64url of 32 bytes is 43 chars (no padding)');
        $this->assertMatchesRegularExpression('/^[A-Za-z0-9_-]+$/', $token, 'token must be URL-safe base64chars only');
        $token2 = generateSecureToken();
        $this->assertNotSame($token, $token2, 'tokens must be unique');
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

    /**
     * @covers ::continuallyExecuteCallable
     */
    public function test_continuallyExecuteCallable_runs_callable_until_maxRunTime(): void
    {
        $ran = false;
        ob_start();
        continuallyExecuteCallable(
            function () use (&$ran): void {
                $ran = true;
            },
            0,
            0,
            0
        );
        $output = ob_get_clean();
        $this->assertTrue($ran, 'callable should have been invoked');
        $this->assertStringContainsString('starting continuallyExecuteCallable', $output);
        $this->assertStringContainsString('Reach maxRunTime', $output);
        $this->assertStringContainsString('Finishing continuallyExecuteCallable', $output);
    }

    /**
     * @covers ::json_decode_safe
     */
    public function test_json_decode_safe()
    {
        $data = ['foo' => 'bar'];
        $output = json_decode_safe(json_encode($data));

        $this->assertSame($data, $output);
    }

    /**
     * @covers ::json_decode_safe
     */
    public function test_json_decode_safe_throws_for_null(): void
    {
        $this->expectException(\Bristolian\Exception\JsonException::class);
        $this->expectExceptionMessage('cannot decode null');
        json_decode_safe(null);
    }

    /**
     * @covers ::json_decode_safe
     */
    public function test_json_decode_safe_throws_for_invalid_json(): void
    {
        $this->expectException(\Seld\JsonLint\ParsingException::class);
        json_decode_safe('{ invalid }');
    }

    /**
     * @covers ::json_decode_safe
     * Triggers path where json_decode fails (e.g. depth) but parser returns null, so we throw JsonException.
     */
    public function test_json_decode_safe_throws_JsonException_when_decode_fails_but_lint_returns_null(): void
    {
        $deeplyNested = str_repeat('[', 600) . '0' . str_repeat(']', 600);
        $this->expectException(\Bristolian\Exception\JsonException::class);
        $this->expectExceptionMessage('Error decoding JSON:');
        json_decode_safe($deeplyNested);
    }

    /**
     * @covers ::json_encode_safe
     */
    public function test_json_encode_safe_returns_json_string_for_valid_data(): void
    {
        $data = ['foo' => 'bar', 'n' => 42];
        $result = json_encode_safe($data);
        $decoded = json_decode($result, true);
        $this->assertSame($data, $decoded);
    }

    /**
     * @covers ::json_encode_safe
     */
    public function test_json_encode_safe_throws_for_unencodable_value(): void
    {
        $this->expectException(\Bristolian\Exception\JsonException::class);
        $this->expectExceptionMessage('Failed to encode data as json');
        json_encode_safe(fopen('php://memory', 'rb'));
    }

    /**
     * @covers ::getExceptionInfoAsArray
     */
    public function test_getExceptionInfoAsArray_returns_structure_for_exception(): void
    {
        $exception = new \RuntimeException('Test message');
        $result = getExceptionInfoAsArray($exception);
        $this->assertSame('error', $result['status']);
        $this->assertSame('Test message', $result['message']);
        $this->assertIsArray($result['details']);
        $this->assertCount(1, $result['details']);
        $this->assertSame(\RuntimeException::class, $result['details'][0]['type']);
        $this->assertSame('Test message', $result['details'][0]['message']);
        $this->assertIsArray($result['details'][0]['trace']);
    }

    /**
     * @covers ::getExceptionInfoAsArray
     */
    public function test_getExceptionInfoAsArray_includes_previous_exceptions(): void
    {
        $previous = new \InvalidArgumentException('Cause');
        $exception = new \RuntimeException('Wrapper', 0, $previous);
        $result = getExceptionInfoAsArray($exception);
        $this->assertCount(2, $result['details']);
        $this->assertSame(\RuntimeException::class, $result['details'][0]['type']);
        $this->assertSame(\InvalidArgumentException::class, $result['details'][1]['type']);
        $this->assertSame('Cause', $result['details'][1]['message']);
    }

    /**
     * @covers ::getReasonPhrase
     */
    public function test_getReasonPhrase_returns_known_phrases(): void
    {
        $this->assertSame('Enhance Your Calm', getReasonPhrase(420));
        $this->assertSame('what the heck', getReasonPhrase(421));
        $this->assertSame('Server known limitation', getReasonPhrase(512));
    }

    /**
     * @covers ::getReasonPhrase
     */
    public function test_getReasonPhrase_returns_empty_string_for_unknown_status(): void
    {
        $this->assertSame('', getReasonPhrase(200));
        $this->assertSame('', getReasonPhrase(999));
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


    /**
     * @covers ::renderTableHtml
     */
    public function test_renderTableHtml_returns_table_with_headers_and_rows(): void
    {
        $headers = ['Name', 'Value'];
        $items = [['name' => 'a', 'val' => 1], ['name' => 'b', 'val' => 2]];
        $rowFns = [
            ':html_name' => fn($item) => $item['name'],
            ':html_val' => fn($item) => (string) $item['val'],
        ];
        $result = renderTableHtml($headers, $items, $rowFns);
        $this->assertStringContainsString('<table>', $result);
        $this->assertStringContainsString('<th>', $result);
        $this->assertStringContainsString('Name', $result);
        $this->assertStringContainsString('Value', $result);
        $this->assertStringContainsString('a', $result);
        $this->assertStringContainsString('1', $result);
        $this->assertStringContainsString('b', $result);
        $this->assertStringContainsString('2', $result);
    }

    /**
     * @covers ::getMask
     */
    public function test_getMask_returns_expected_values(): void
    {
        $this->assertSame(0x2, getMask('sign'));
        $this->assertSame(0x800, getMask('exponent'));
        $this->assertSame(0x80000000000000, getMask('mantissa'));
    }

    /**
     * @covers ::getMask
     */
    public function test_getMask_throws_for_unknown_name(): void
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Unknown type');
        getMask('unknown');
    }

    /**
     * @covers ::human_readable_value
     */
    public function test_human_readable_value_formats_sizes(): void
    {
        $this->assertSame('500 B', human_readable_value(500));
        $this->assertSame('1.5 KB', human_readable_value(1536));
        $this->assertSame('1 MB', human_readable_value(1024 * 1024));
    }

    public function test_convertToValue()
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
     * @covers ::createJsonResponse
     */
    public function test_createJsonResponse_returns_success_response_for_convertible_data(): void
    {
        $response = createJsonResponse(['key' => 'value']);
        $this->assertInstanceOf(\SlimDispatcher\Response\JsonNoCacheResponse::class, $response);
        $payload = json_decode((string) $response->getBody(), true);
        $this->assertSame('success', $payload['result']);
        $this->assertSame(['key' => 'value'], $payload['data']);
    }

    /**
     * @covers ::createJsonResponse
     */
    public function test_createJsonResponse_returns_failure_response_when_convertToValue_fails(): void
    {
        $response = createJsonResponse(['bad' => fopen('php://memory', 'rb')]);
        $this->assertInstanceOf(\SlimDispatcher\Response\JsonNoCacheResponse::class, $response);
        $this->assertSame(500, $response->getStatus());
        $payload = json_decode((string) $response->getBody(), true);
        $this->assertSame('failure', $payload['result']);
        $this->assertArrayHasKey('error', $payload);
    }

    /**
     * @covers ::getRouteForStoredFile
     */
    public function test_getRouteForStoredFile_returns_esprintf_route(): void
    {
        $storedFile = new \Bristolian\Model\Generated\RoomFileObjectInfo(
            'file-id-123',
            'normalized.pdf',
            'Original Name.pdf',
            'active',
            1024,
            'user_1',
            new \DateTimeImmutable()
        );
        $result = getRouteForStoredFile('room_456', $storedFile);
        $this->assertStringContainsString('/rooms/', $result);
        $this->assertStringContainsString('room_456', $result);
        $this->assertStringContainsString('file-id-123', $result);
        $this->assertMatchesRegularExpression('/Original(.+)Name\.pdf$/', $result, 'filename is URI-encoded in route');
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

    /**
     * @covers ::generate_password_hash
     */
    public function test_generate_password_hash_returns_bcrypt_hash_verifiable_with_password(): void
    {
        $password = 'Hello';
        $hash = generate_password_hash($password);
        $this->assertSame(60, strlen($hash), 'bcrypt hashes are 60 characters');
        $this->assertTrue(password_verify($password, $hash), 'hash must verify against original password');
        $this->assertFalse(password_verify('wrong', $hash), 'hash must not verify against wrong password');
        $hash2 = generate_password_hash($password);
        $this->assertNotSame($hash, $hash2, 'each call should produce a different hash (salted)');
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

    public static function provides_extract_youtube_video_id(): \Generator
    {
        $videoId = 'dQw4w9WgXcQ';
        yield 'youtu.be short' => ['https://youtu.be/' . $videoId, $videoId];
        yield 'youtu.be with t param' => ['https://youtu.be/' . $videoId . '?t=123', $videoId];
        yield 'youtube.com watch' => ['https://www.youtube.com/watch?v=' . $videoId, $videoId];
        yield 'youtube.com watch with other params' => ['https://youtube.com/watch?foo=bar&v=' . $videoId . '&list=abc', $videoId];
        yield 'youtube.com embed' => ['https://www.youtube.com/embed/' . $videoId, $videoId];
        yield 'youtube.com v path' => ['https://youtube.com/v/' . $videoId, $videoId];
        yield 'raw 11-char id' => [$videoId, $videoId];
        yield 'id with underscore and hyphen' => ['https://youtu.be/ABcd_12-EFg', 'ABcd_12-EFg'];
        yield 'empty string' => ['', null];
        yield 'whitespace only' => ['   ', null];
        yield 'not a youtube url' => ['https://example.com/foo', null];
        yield 'youtube url with short id' => ['https://youtu.be/abc', null];
        yield 'invalid url no id' => ['https://www.youtube.com/watch', null];
    }

    /**
     * @dataProvider provides_extract_youtube_video_id
     * @covers ::extract_youtube_video_id
     */
    public function test_extract_youtube_video_id(string $url, ?string $expected): void
    {
        $this->assertSame($expected, extract_youtube_video_id($url));
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
        yield [null, "null"];
        yield [true, "a bool"];
        yield [3.14, "a float"];
        yield [[1, 2], "an array"];
        yield [fopen("php://memory", "r"), "resource (stream)"];
    }

    /**
     * @covers ::get_readable_variable_type
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

    /**
     * @covers ::get_supported_bristolian_stair_image_extensions
     */
    public function test_get_supported_bristolian_stair_image_extensions(): void
    {
        $extensions = get_supported_bristolian_stair_image_extensions();
        $this->assertContains('jpeg', $extensions);
        $this->assertContains('jpg', $extensions);
    }

    /**
     * @covers ::get_supported_avatar_image_extensions
     */
    public function test_get_supported_avatar_image_extensions(): void
    {
        $extensions = get_supported_avatar_image_extensions();
        $this->assertContains('jpeg', $extensions);
        $this->assertContains('jpg', $extensions);
        $this->assertContains('png', $extensions);
    }

    /**
     * @covers ::get_supported_meme_file_extensions
     */
    public function test_get_supported_meme_file_extensions_returns_expected_list(): void
    {
        $extensions = get_supported_meme_file_extensions();
        $this->assertContains('gif', $extensions);
        $this->assertContains('jpg', $extensions);
        $this->assertContains('jpeg', $extensions);
        $this->assertContains('mp4', $extensions);
        $this->assertContains('png', $extensions);
        $this->assertContains('pdf', $extensions);
        $this->assertContains('webp', $extensions);
        $this->assertCount(7, $extensions);
    }

    /**
     * @covers ::encodeWidgetyData
     */
    public function test_encodeWidgetyData_escapes_json(): void
    {
        $data = ['key' => 'value'];
        $result = encodeWidgetyData($data);
        $this->assertStringContainsString('key', $result);
        $this->assertStringContainsString('value', $result);
    }

    /**
     * @covers ::get_mime_type_from_extension
     */
    public function test_get_mime_type_from_extension_returns_null_for_unknown(): void
    {
        $this->assertNull(get_mime_type_from_extension('xyz'));
    }

    /**
     * @covers ::get_mime_type_from_extension
     */
    public function test_get_mime_type_from_extension_returns_type_for_known(): void
    {
        $this->assertSame('image/jpeg', get_mime_type_from_extension('jpg'));
    }

    /**
     * @covers ::getMimeTypeFromFilename
     */
    public function test_getMimeTypeFromFilename_returns_mime_type_for_known_extension(): void
    {
        $this->assertSame('image/jpeg', getMimeTypeFromFilename('photo.jpg'));
        $this->assertSame('application/pdf', getMimeTypeFromFilename('doc.PDF'));
    }

    /**
     * @covers ::getMimeTypeFromFilename
     */
    public function test_getMimeTypeFromFilename_throws_for_unknown_extension(): void
    {
        $this->expectException(\Bristolian\Exception\BristolianException::class);
        $this->expectExceptionMessage('Unknown file type');
        getMimeTypeFromFilename('file.unknown');
    }

    /**
     * @covers ::getEnumCases
     */
    public function test_getEnumCases_returns_cases_for_enum(): void
    {
        $cases = getEnumCases(DocumentType::class);
        $this->assertNotEmpty($cases);
        $this->assertContainsOnlyInstancesOf(\UnitEnum::class, $cases);
    }

    /**
     * @covers ::getEnumCases
     */
    public function test_getEnumCases_throws_for_non_existent_class(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('does not exist');
        /** @phpstan-ignore argument.type (intentionally invalid to test exception) */
        getEnumCases('NonExistentClass');
    }

    /**
     * @covers ::getEnumCases
     */
    public function test_getEnumCases_throws_for_non_enum_class(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('is not an enum');
        /** @phpstan-ignore argument.type (intentionally invalid to test exception) */
        getEnumCases(\StdClass::class);
    }

    /**
     * @covers ::getEnumCaseValues
     */
    public function test_getEnumCaseValues_returns_values_for_enum(): void
    {
        $values = getEnumCaseValues(DocumentType::class);
        $this->assertNotEmpty($values);
    }

    /**
     * @covers ::getEnumCaseValues
     */
    public function test_getEnumCaseValues_throws_for_non_existent_class(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        /** @phpstan-ignore argument.type (intentionally invalid to test exception) */
        getEnumCaseValues('NonExistentClass');
    }

    /**
     * @covers ::getEnumCaseValues
     */
    public function test_getEnumCaseValues_throws_for_non_enum_class(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('is not an enum');
        /** @phpstan-ignore argument.type (intentionally invalid to test exception) */
        getEnumCaseValues(\StdClass::class);
    }

    /**
     * @covers ::createBlankUserProfileForUserId
     */
    public function test_createBlankUserProfileForUserId(): void
    {
        $profile = createBlankUserProfileForUserId('user_123');
        $this->assertInstanceOf(\Bristolian\Model\Generated\UserProfile::class, $profile);
        $this->assertSame('user_123', $profile->user_id);
        $this->assertNull($profile->avatar_image_id);
        $this->assertNull($profile->about_me);
    }

    /**
     * @covers ::ensureFileCachedFromString
     */
    public function test_ensureFileCachedFromString_returns_cached_content_when_file_exists(): void
    {
        $cacheDir = sys_get_temp_dir() . '/fc_cache_' . uniqid();
        $sourceDir = sys_get_temp_dir() . '/fc_src_' . uniqid();
        mkdir($cacheDir, 0755, true);
        mkdir($sourceDir, 0755, true);
        try {
            $localCache = new \Bristolian\Filesystem\LocalCacheFilesystem(
                new LocalFilesystemAdapter($cacheDir),
                $cacheDir
            );
            $source = new Filesystem(new LocalFilesystemAdapter($sourceDir));
            $localCache->write('cached.txt', 'from-cache');
            $result = ensureFileCachedFromString($localCache, $source, 'cached.txt');
            $this->assertSame('from-cache', $result);
        } finally {
            $this->rrmdir($cacheDir);
            $this->rrmdir($sourceDir);
        }
    }

    /**
     * @covers ::ensureFileCachedFromString
     */
    public function test_ensureFileCachedFromString_reads_from_source_and_writes_to_cache_when_missing(): void
    {
        $cacheDir = sys_get_temp_dir() . '/fc_cache2_' . uniqid();
        $sourceDir = sys_get_temp_dir() . '/fc_src2_' . uniqid();
        mkdir($cacheDir, 0755, true);
        mkdir($sourceDir, 0755, true);
        try {
            $localCache = new \Bristolian\Filesystem\LocalCacheFilesystem(
                new LocalFilesystemAdapter($cacheDir),
                $cacheDir
            );
            $source = new Filesystem(new LocalFilesystemAdapter($sourceDir));
            $source->write('fetch.txt', 'from-source');
            $result = ensureFileCachedFromString($localCache, $source, 'fetch.txt');
            $this->assertSame('from-source', $result);
            $this->assertSame('from-source', $localCache->read('fetch.txt'));
        } finally {
            $this->rrmdir($cacheDir);
            $this->rrmdir($sourceDir);
        }
    }

    /**
     * @covers ::ensureFileCachedFromStream
     */
    public function test_ensureFileCachedFromStream_writes_to_cache_when_file_not_cached(): void
    {
        $cacheDir = sys_get_temp_dir() . '/fc_stream_cache_' . uniqid();
        $sourceDir = sys_get_temp_dir() . '/fc_stream_src_' . uniqid();
        mkdir($cacheDir, 0755, true);
        mkdir($sourceDir, 0755, true);
        try {
            $localCache = new \Bristolian\Filesystem\LocalCacheFilesystem(
                new LocalFilesystemAdapter($cacheDir),
                $cacheDir
            );
            $source = new Filesystem(new LocalFilesystemAdapter($sourceDir));
            $source->write('stream.txt', 'streamed content');
            ensureFileCachedFromStream($localCache, $source, 'stream.txt');
            $this->assertTrue($localCache->fileExists('stream.txt'));
            $this->assertSame('streamed content', $localCache->read('stream.txt'));
        } finally {
            $this->rrmdir($cacheDir);
            $this->rrmdir($sourceDir);
        }
    }

    /**
     * @covers ::ensureFileCachedFromStream
     */
    public function test_ensureFileCachedFromStream_does_nothing_when_already_cached(): void
    {
        $cacheDir = sys_get_temp_dir() . '/fc_stream_c2_' . uniqid();
        $sourceDir = sys_get_temp_dir() . '/fc_stream_s2_' . uniqid();
        mkdir($cacheDir, 0755, true);
        mkdir($sourceDir, 0755, true);
        try {
            $localCache = new \Bristolian\Filesystem\LocalCacheFilesystem(
                new LocalFilesystemAdapter($cacheDir),
                $cacheDir
            );
            $source = new Filesystem(new LocalFilesystemAdapter($sourceDir));
            $localCache->write('already.txt', 'cached');
            $source->write('already.txt', 'different');
            ensureFileCachedFromStream($localCache, $source, 'already.txt');
            $this->assertSame('cached', $localCache->read('already.txt'));
        } finally {
            $this->rrmdir($cacheDir);
            $this->rrmdir($sourceDir);
        }
    }

    /**
     * @covers ::generateSystemInfoEmailContent
     */
    public function test_generateSystemInfoEmailContent_returns_body_with_shamoan_and_disk_info(): void
    {
        $result = generateSystemInfoEmailContent();
        $this->assertStringStartsWith('Shamoan', $result);
        $this->assertStringContainsString("\n\n", $result);
    }

    /**
     * @covers ::mapStreamingResponseToPSR7
     */
    public function test_mapStreamingResponseToPSR7_returns_psr7_response_with_status_and_body(): void
    {
        $filepath = __DIR__ . '/../sample.pdf';
        $streamingResponse = new \Bristolian\Response\StreamingResponse($filepath);
        $response = mapStreamingResponseToPSR7($streamingResponse);
        $this->assertInstanceOf(\Psr\Http\Message\ResponseInterface::class, $response);
        $this->assertSame(200, $response->getStatusCode());
        $this->assertTrue($response->hasHeader('Content-Type'));
        $body = (string) $response->getBody();
        $this->assertSame(\Safe\file_get_contents($filepath), $body);
    }

    private function rrmdir(string $dir): void
    {
        if (!is_dir($dir)) {
            return;
        }
        foreach (scandir($dir) ?: [] as $item) {
            if ($item === '.' || $item === '..') {
                continue;
            }
            $path = $dir . '/' . $item;
            is_dir($path) ? $this->rrmdir($path) : @unlink($path);
        }
        @rmdir($dir);
    }
}
