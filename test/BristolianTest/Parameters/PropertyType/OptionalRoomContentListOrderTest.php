<?php

declare(strict_types=1);

namespace BristolianTest\Parameters\PropertyType;

use Bristolian\Parameters\PropertyType\OptionalRoomContentListOrder;
use BristolianTest\BaseTestCase;
use DataType\Create\CreateFromVarMap;
use DataType\DataType;
use DataType\ExtractRule\GetOptionalString;
use DataType\GetInputTypesFromAttributes;
use DataType\Messages;
use DataType\ProcessRule\NullIfEmptyString;
use DataType\ProcessRule\Order;
use DataType\ProcessRule\SkipIfNull;
use DataType\Value\Ordering;
use VarMap\ArrayVarMap;

/**
 * @coversNothing
 */
class OptionalRoomContentListOrderTest extends BaseTestCase
{
    /**
     * @return \Generator<string, array{array<string, mixed>, Ordering|null}>
     */
    public static function provides_valid_input_and_expected_output(): \Generator
    {
        yield 'missing key returns null' => [[], null];
        yield 'empty string returns null' => [['order' => ''], null];
        yield 'single ascending order' => [['order' => 'name'], new Ordering([
            new \DataType\Value\OrderElement('name', Ordering::ASC),
        ])];
        yield 'multiple explicit directions' => [['order' => '+size,-document_date'], new Ordering([
            new \DataType\Value\OrderElement('size', Ordering::ASC),
            new \DataType\Value\OrderElement('document_date', Ordering::DESC),
        ])];
    }

    /**
     * @covers \Bristolian\Parameters\PropertyType\OptionalRoomContentListOrder::__construct
     * @covers \Bristolian\Parameters\PropertyType\OptionalRoomContentListOrder::getInputType
     * @dataProvider provides_valid_input_and_expected_output
     * @param array<string, mixed> $input
     */
    public function test_parses_valid_input_to_expected_output(array $input, Ordering|null $expectedValue): void
    {
        $params = OptionalRoomContentListOrderFixture::createFromVarMap(new ArrayVarMap($input));

        if ($expectedValue === null) {
            $this->assertNull($params->order);
            return;
        }

        $this->assertInstanceOf(Ordering::class, $params->order);
        $this->assertSame($expectedValue->toOrderArray(), $params->order->toOrderArray());
    }

    /**
     * @covers \Bristolian\Parameters\PropertyType\OptionalRoomContentListOrder::__construct
     * @covers \Bristolian\Parameters\PropertyType\OptionalRoomContentListOrder::getInputType
     */
    public function test_rejects_unknown_order_name(): void
    {
        try {
            OptionalRoomContentListOrderFixture::createFromVarMap(new ArrayVarMap([
                'order' => 'unknown',
            ]));
            $this->fail('Expected ValidationException was not thrown.');
        } catch (\DataType\Exception\ValidationException $validationException) {
            $this->assertValidationProblems(
                $validationException->getValidationProblems(),
                [
                    '/order' => sprintf(
                        Messages::ORDER_VALUE_UNKNOWN,
                        'unknown',
                        implode(', ', OptionalRoomContentListOrder::knownOrderNames())
                    ),
                ]
            );
        }
    }

    /**
     * @covers \Bristolian\Parameters\PropertyType\OptionalRoomContentListOrder::__construct
     * @covers \Bristolian\Parameters\PropertyType\OptionalRoomContentListOrder::knownOrderNames
     * @covers \Bristolian\Parameters\PropertyType\OptionalRoomContentListOrder::getInputType
     */
    public function test_getInputType_uses_expected_name_and_rules(): void
    {
        $propertyType = new OptionalRoomContentListOrder('sort');
        $inputType = $propertyType->getInputType();

        $this->assertSame('sort', $inputType->getName());
        $this->assertSame(
            ['name', 'size', 'added', 'document_date'],
            OptionalRoomContentListOrder::knownOrderNames()
        );
        $this->assertInstanceOf(GetOptionalString::class, $inputType->getExtractRule());

        $processRules = $inputType->getProcessRules();
        $this->assertCount(3, $processRules);
        $this->assertInstanceOf(NullIfEmptyString::class, $processRules[0]);
        $this->assertInstanceOf(SkipIfNull::class, $processRules[1]);
        $this->assertInstanceOf(Order::class, $processRules[2]);
    }
}

class OptionalRoomContentListOrderFixture implements DataType
{
    use CreateFromVarMap;
    use GetInputTypesFromAttributes;

    public function __construct(
        #[OptionalRoomContentListOrder('order')]
        public readonly Ordering|null $order,
    ) {
    }
}
