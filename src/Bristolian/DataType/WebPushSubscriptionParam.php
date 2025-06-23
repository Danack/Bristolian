<?php

namespace Bristolian\DataType;

use Bristolian\DataType\PropertyType\BasicString;
use Bristolian\DataType\PropertyType\WebPushEndPoint;
use Bristolian\DataType\PropertyType\WebPushExpirationTime;
use DataType\Create\CreateFromArray;
use DataType\Create\CreateFromJson;
use DataType\Create\CreateFromVarMap;
use DataType\Create\CreateOrErrorFromArray;
use DataType\DataType;
use DataType\GetInputTypesFromAttributes;

class WebPushSubscriptionParam implements DataType
{
    use CreateFromVarMap;
    use CreateFromJson;
    use CreateFromArray;
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
