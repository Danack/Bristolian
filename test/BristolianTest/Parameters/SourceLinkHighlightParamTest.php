<?php

declare(strict_types = 1);

namespace BristolianTest\Parameters;

use BristolianTest\BaseTestCase;
use Bristolian\Parameters\SourceLinkHighlightParam;
use DataType\Messages;
use VarMap\ArrayVarMap;

/**
 * @covers \Bristolian\Parameters\SourceLinkHighlightParam
 */
class SourceLinkHighlightParamTest extends BaseTestCase
{
    public function testWorks()
    {
        $page = 1;
        $left = 100;
        $top = 200;
        $right = 300;
        $bottom = 400;

        $params = [
            'page' => $page,
            'left' => $left,
            'top' => $top,
            'right' => $right,
            'bottom' => $bottom,
        ];

        $sourceLinkHighlightParam = SourceLinkHighlightParam::createFromVarMap(new ArrayVarMap($params));

        $this->assertSame($page, $sourceLinkHighlightParam->page);
        $this->assertSame($left, $sourceLinkHighlightParam->left);
        $this->assertSame($top, $sourceLinkHighlightParam->top);
        $this->assertSame($right, $sourceLinkHighlightParam->right);
        $this->assertSame($bottom, $sourceLinkHighlightParam->bottom);
    }

    public function testFailsWithMissingPage()
    {
        try {
            $params = [
                'left' => 100,
                'top' => 200,
                'right' => 300,
                'bottom' => 400,
            ];

            SourceLinkHighlightParam::createFromVarMap(new ArrayVarMap($params));
            $this->fail("Expected ValidationException was not thrown.");
        }
        catch (\DataType\Exception\ValidationException $ve) {
            $this->assertValidationProblems(
                $ve->getValidationProblems(),
                ['/page' => Messages::VALUE_NOT_SET]
            );
        }
    }

    public function testFailsWithMissingLeft()
    {
        try {
            $params = [
                'page' => 1,
                'top' => 200,
                'right' => 300,
                'bottom' => 400,
            ];

            SourceLinkHighlightParam::createFromVarMap(new ArrayVarMap($params));
            $this->fail("Expected ValidationException was not thrown.");
        }
        catch (\DataType\Exception\ValidationException $ve) {
            $this->assertValidationProblems(
                $ve->getValidationProblems(),
                ['/left' => Messages::VALUE_NOT_SET]
            );
        }
    }

    public function testFailsWithMissingTop()
    {
        try {
            $params = [
                'page' => 1,
                'left' => 100,
                'right' => 300,
                'bottom' => 400,
            ];

            SourceLinkHighlightParam::createFromVarMap(new ArrayVarMap($params));
            $this->fail("Expected ValidationException was not thrown.");
        }
        catch (\DataType\Exception\ValidationException $ve) {
            $this->assertValidationProblems(
                $ve->getValidationProblems(),
                ['/top' => Messages::VALUE_NOT_SET]
            );
        }
    }

    public function testFailsWithMissingRight()
    {
        try {
            $params = [
                'page' => 1,
                'left' => 100,
                'top' => 200,
                'bottom' => 400,
            ];

            SourceLinkHighlightParam::createFromVarMap(new ArrayVarMap($params));
            $this->fail("Expected ValidationException was not thrown.");
        }
        catch (\DataType\Exception\ValidationException $ve) {
            $this->assertValidationProblems(
                $ve->getValidationProblems(),
                ['/right' => Messages::VALUE_NOT_SET]
            );
        }
    }

    public function testFailsWithMissingBottom()
    {
        try {
            $params = [
                'page' => 1,
                'left' => 100,
                'top' => 200,
                'right' => 300,
            ];

            SourceLinkHighlightParam::createFromVarMap(new ArrayVarMap($params));
            $this->fail("Expected ValidationException was not thrown.");
        }
        catch (\DataType\Exception\ValidationException $ve) {
            $this->assertValidationProblems(
                $ve->getValidationProblems(),
                ['/bottom' => Messages::VALUE_NOT_SET]
            );
        }
    }

    public function testFailsWithAllMissing()
    {
        try {
            $params = [];

            SourceLinkHighlightParam::createFromVarMap(new ArrayVarMap($params));
            $this->fail("Expected ValidationException was not thrown.");
        }
        catch (\DataType\Exception\ValidationException $ve) {
            $this->assertValidationProblems(
                $ve->getValidationProblems(),
                [
                    '/page' => Messages::VALUE_NOT_SET,
                    '/left' => Messages::VALUE_NOT_SET,
                    '/top' => Messages::VALUE_NOT_SET,
                    '/right' => Messages::VALUE_NOT_SET,
                    '/bottom' => Messages::VALUE_NOT_SET
                ]
            );
        }
    }

    public function testFailsWithInvalidDataTypes()
    {
        try {
            $params = [
                'page' => 'invalid',
                'left' => 'invalid',
                'top' => 'invalid',
                'right' => 'invalid',
                'bottom' => 'invalid',
            ];

            SourceLinkHighlightParam::createFromVarMap(new ArrayVarMap($params));
            $this->fail("Expected ValidationException was not thrown.");
        }
        catch (\DataType\Exception\ValidationException $ve) {
            $this->assertValidationProblems(
                $ve->getValidationProblems(),
                [
                    '/page' => Messages::INT_REQUIRED_FOUND_NON_DIGITS2,
                    '/left' => Messages::INT_REQUIRED_FOUND_NON_DIGITS2,
                    '/top' => Messages::INT_REQUIRED_FOUND_NON_DIGITS2,
                    '/right' => Messages::INT_REQUIRED_FOUND_NON_DIGITS2,
                    '/bottom' => Messages::INT_REQUIRED_FOUND_NON_DIGITS2
                ]
            );
        }
    }

    public function testFailsWithNullValues()
    {
        try {
            $params = [
                'page' => null,
                'left' => null,
                'top' => null,
                'right' => null,
                'bottom' => null,
            ];

            SourceLinkHighlightParam::createFromVarMap(new ArrayVarMap($params));
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
            'page' => 1,
            'left' => 100,
            'top' => 200,
            'right' => 300,
            'bottom' => 400,
        ];

        $sourceLinkHighlightParam = SourceLinkHighlightParam::createFromVarMap(new ArrayVarMap($params));

        $this->assertInstanceOf(\DataType\DataType::class, $sourceLinkHighlightParam);
    }
}
