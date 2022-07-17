<?php

declare(strict_types=1);



> Do you have an example where the intent would be less
> obvious ? With code you would actually write ?



function get_shipping_calculator_for_user($user): ShippingCalculator {}

interface ShippingCalculator
{
    function addItem(Item $item): self;
    function addCostCalculator(ShippingCalculator $calculator): self;
    function getTotalPrice();
}

function calculate_shipping_cost($user, $items)
{
    $shipping_calculator = get_shipping_calculator_for_user($user);

    $fn = function($item) use ($shipping_calculator)  {
      // more logic here
      // $shipping_calculator can be used safely here as:
      // "A by-value capture means that it is not possible to modify
      // any variables from the outer scope:"
      // - https://wiki.php.net/rfc/auto-capture-closure#capture_is_by-value

      $shipping_calculator->add($item);
      // more logic here

      return $shipping_calculator;
    };

    foreach ($items as $item) {
        $shipping_calculator->add($fn($item));
    }

    return $shipping_calculator->getTotalPrice();
}
