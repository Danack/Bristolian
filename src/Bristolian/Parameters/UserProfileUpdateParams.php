<?php

namespace Bristolian\Parameters;

use Bristolian\Parameters\PropertyType\BasicString;
use Bristolian\StaticFactory;
use DataType\Create\CreateFromRequest;
use DataType\Create\CreateFromVarMap;
use DataType\DataType;
use DataType\GetInputTypesFromAttributes;
use Bristolian\Parameters\PropertyType\DisplayName;
use Bristolian\Parameters\PropertyType\AboutMeText;

class UserProfileUpdateParams implements DataType, StaticFactory
{
    use CreateFromRequest;
    use CreateFromVarMap;
    use GetInputTypesFromAttributes;

    public function __construct(
        #[DisplayName('display_name')]
        public readonly string $display_name,
        #[AboutMeText('about_me')]
        public readonly string $about_me,
    ) {
    }
}
