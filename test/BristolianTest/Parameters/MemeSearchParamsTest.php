<?php

declare(strict_types = 1);

namespace BristolianTest\Parameters;

use Bristolian\Parameters\MemeSearchParams;
use BristolianTest\BaseTestCase;
use VarMap\ArrayVarMap;

/**
 * @coversNothing
 */
class MemeSearchParamsTest extends BaseTestCase
{
    /**
     * @return \Generator<string, array{array<string, mixed>, string|null, string|null, string|null, string|null}>
     */
    public static function provides_input_and_expected_output(): \Generator
    {
        yield 'all missing' => [[], null, null, null, null];
        yield 'query only' => [['query' => 'search'], 'search', null, null, null];
        yield 'tag_type only' => [['tag_type' => 'user'], null, 'user', null, null];
        yield 'text_search only' => [['text_search' => 'phrase'], null, null, 'phrase', null];
        yield 'tags only' => [['tags' => 'tag1,tag2'], null, null, null, 'tag1,tag2'];
        yield 'all provided' => [
            ['query' => 'q', 'tag_type' => 't', 'text_search' => 'ts', 'tags' => 'a,b'],
            'q', 't', 'ts', 'a,b',
        ];
    }

    /**
     * @covers \Bristolian\Parameters\MemeSearchParams
     * @covers \Bristolian\Parameters\PropertyType\OptionalBasicString
     * @dataProvider provides_input_and_expected_output
     * @param array<string, mixed> $input
     */
    public function test_parses_input_to_expected_output(
        array $input,
        ?string $expectedQuery,
        ?string $expectedTagType,
        ?string $expectedTextSearch,
        ?string $expectedTags
    ): void {
        $params = MemeSearchParams::createFromVarMap(new ArrayVarMap($input));

        $this->assertSame($expectedQuery, $params->query);
        $this->assertSame($expectedTagType, $params->tag_type);
        $this->assertSame($expectedTextSearch, $params->text_search);
        $this->assertSame($expectedTags, $params->tags);
    }
}
