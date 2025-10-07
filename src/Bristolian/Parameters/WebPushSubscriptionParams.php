<?php

namespace Bristolian\Parameters;

use Bristolian\Parameters\PropertyType\BasicString;
use Bristolian\Parameters\PropertyType\WebPushEndPoint;
use Bristolian\Parameters\PropertyType\WebPushExpirationTime;
use Bristolian\StaticFactory;
use DataType\Create\CreateFromArray;
use DataType\Create\CreateFromJson;
use DataType\Create\CreateFromRequest;
use DataType\Create\CreateFromVarMap;
use DataType\Create\CreateOrErrorFromArray;
use DataType\DataType;
use DataType\GetInputTypesFromAttributes;

class WebPushSubscriptionParams implements DataType, StaticFactory
{
    use CreateFromVarMap;
    use CreateFromJson;
    use CreateFromArray;
    use CreateFromRequest;
    use CreateOrErrorFromArray;
    use GetInputTypesFromAttributes;

    public function __construct(
        #[WebPushEndPoint('endpoint')]
        public string $endpoint,
        #[WebPushExpirationTime('expirationTime')]
        public string|null $expiration_time,
        #[BasicString('raw')]
        public string $raw,
    ) {
    }

    /**
     * @return string
     */
    public function getEndpoint(): string
    {
        return $this->endpoint;
    }

    /**
     * @return string|null
     */
    public function getExpirationTime(): string|null
    {
        return $this->expiration_time;
    }

    /**
     * @return string
     */
    public function getRaw(): string
    {
        return $this->raw;
    }
}
