<?php

declare(strict_types = 1);

namespace BristolianTest\Parameters;

use BristolianTest\BaseTestCase;
use Bristolian\Parameters\SourceLinkParam;
use DataType\Messages;
use VarMap\ArrayVarMap;

/**
 * @covers \Bristolian\Parameters\SourceLinkParam
 */
class SourceLinkParamTest extends BaseTestCase
{
    public function testWorks()
    {
        $title = 'This is a longer source title that meets the minimum length requirement';
        $highlights_json = '{"highlights": []}';
        $text = 'Source text content';

        $params = [
            'title' => $title,
            'highlights_json' => $highlights_json,
            'text' => $text,
        ];

        $sourceLinkParam = SourceLinkParam::createFromVarMap(new ArrayVarMap($params));

        $this->assertSame($title, $sourceLinkParam->title);
        $this->assertSame($highlights_json, $sourceLinkParam->highlights_json);
        $this->assertSame($text, $sourceLinkParam->text);
    }

    public function testFailsWithMissingTitle()
    {
        try {
            $params = [
                'highlights_json' => '{"highlights": []}',
                'text' => 'Source text content',
            ];

            SourceLinkParam::createFromVarMap(new ArrayVarMap($params));
            $this->fail("Expected ValidationException was not thrown.");
        }
        catch (\DataType\Exception\ValidationException $ve) {
            $this->assertValidationProblems(
                $ve->getValidationProblems(),
                ['/title' => Messages::VALUE_NOT_SET]
            );
        }
    }

    public function testFailsWithMissingHighlightsJson()
    {
        try {
            $params = [
                'title' => 'This is a longer source title that meets the minimum length requirement',
                'text' => 'Source text content',
            ];

            SourceLinkParam::createFromVarMap(new ArrayVarMap($params));
            $this->fail("Expected ValidationException was not thrown.");
        }
        catch (\DataType\Exception\ValidationException $ve) {
            $this->assertValidationProblems(
                $ve->getValidationProblems(),
                ['/highlights_json' => Messages::VALUE_NOT_SET]
            );
        }
    }

    public function testFailsWithMissingText()
    {
        try {
            $params = [
                'title' => 'This is a longer source title that meets the minimum length requirement',
                'highlights_json' => '{"highlights": []}',
            ];

            SourceLinkParam::createFromVarMap(new ArrayVarMap($params));
            $this->fail("Expected ValidationException was not thrown.");
        }
        catch (\DataType\Exception\ValidationException $ve) {
            $this->assertValidationProblems(
                $ve->getValidationProblems(),
                ['/text' => Messages::VALUE_NOT_SET]
            );
        }
    }

    public function testFailsWithInvalidDataTypes()
    {
        try {
            $params = [
                'title' => 123,
                'highlights_json' => 456,
                'text' => 789,
            ];

            SourceLinkParam::createFromVarMap(new ArrayVarMap($params));
            $this->fail("Expected ValidationException was not thrown.");
        }
        catch (\DataType\Exception\ValidationException $ve) {
            $validationProblems = $ve->getValidationProblems();
            $this->assertGreaterThan(0, count($validationProblems));
        }
    }

    public function testImplementsDataTypeInterface()
    {
        $params = [
            'title' => 'This is a longer source title that meets the minimum length requirement',
            'highlights_json' => '{"highlights": []}',
            'text' => 'Source text content',
        ];

        $sourceLinkParam = SourceLinkParam::createFromVarMap(new ArrayVarMap($params));

        $this->assertInstanceOf(\DataType\DataType::class, $sourceLinkParam);
    }
}
