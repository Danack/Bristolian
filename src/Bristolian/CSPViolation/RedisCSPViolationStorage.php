<?php

declare(strict_types = 1);

namespace Bristolian\CSPViolation;

use Bristolian\Data\ContentPolicyViolationReport;
use Bristolian\Key\ContentSecurityPolicyKey;
use Redis;

class RedisCSPViolationStorage implements CSPViolationStorage
{
    /** @var \Redis  */
    private $redis;

    public function __construct(Redis $redis)
    {
        $this->redis = $redis;
    }

    /**
     * this should be called reportViolation?
     */
    public function report(ContentPolicyViolationReport $cpvr): void
    {
        $string = json_encode_safe($cpvr->toArray());
        $this->redis->lPush(
            ContentSecurityPolicyKey::getAbsoluteKeyName('csp'),
            $string
        );
    }

    public function clearReports(): void
    {
        $this->redis->del(ContentSecurityPolicyKey::getAbsoluteKeyName('csp'));
    }

    /**
     * @return ContentPolicyViolationReport[]
     * @throws \Bristolian\Exception\JsonException
     */
    public function getReports(): array
    {
        $elements = $this->redis->lrange(
            ContentSecurityPolicyKey::getAbsoluteKeyName('csp'),
            0,
            49
        );
        $data = [];

        foreach ($elements as $element) {
            $datum = json_decode_safe($element);
            $data[] = ContentPolicyViolationReport::fromArray($datum);
        }

        return $data;
    }

    public function getCount() : int
    {
        $result = $this->redis->llen(
            ContentSecurityPolicyKey::getAbsoluteKeyName('csp')
        );

        if ($result === false) {
            return 0;
        }

        return $result;
    }
}
