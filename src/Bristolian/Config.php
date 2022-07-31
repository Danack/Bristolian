<?php

namespace Bristolian;

class Config
{
    const BRISTOLIAN_ASSETS_FORCE_REFRESH = 'bristol_org.force_assets_refresh';
    const BRISTOLIAN_COMMIT_SHA = 'bristol_org.sha';
    const BRISTOLIAN_DEPLOY_TIME = 'bristol_org.deploy_time';
    const BRISTOLIAN_ENVIRONMENT = 'bristol_org.env';
    const BRISTOLIAN_REDIS_INFO = 'bristol_org.redis_info';

    private $values = [];

    public function __construct()
    {
        $this->values = getGeneratedConfig();
    }

    /**
     * @param $key
     * @return mixed
     * @throws \Exception
     */
    private function get($key)
    {
        if (array_key_exists($key, $this->values) == false) {
            throw new \Exception("No value for " . $key);
        }

        return $this->values[$key];
    }

    /**
     * TODO - return a list of errors, not just report one.
     * @throws \Exception
     */
    public static function testValuesArePresent(): void
    {
        $rc = new \ReflectionClass(self::class);
        $constants = $rc->getConstants();

        $instance = new self();

        foreach ($constants as $constant) {
            $value = $instance->get($constant);
        }
    }

    public function getVersion(): string
    {
        return $this->get(self::BRISTOLIAN_ENVIRONMENT) . "_" . self::get(self::BRISTOLIAN_COMMIT_SHA);
    }

    public function getRedisInfo(): array
    {
//        ['host'],
//        ['port'],
//        ['password']);

        return $this->get(self::BRISTOLIAN_REDIS_INFO);
    }

    public function getDeployTime(): string
    {
        return $this->get(self::BRISTOLIAN_DEPLOY_TIME);
    }

    public function getEnvironment(): string
    {
        return $this->get(self::BRISTOLIAN_ENVIRONMENT);
    }

    public function isProductionEnv(): bool
    {
        if ($this->getEnvironment() === App::ENVIRONMENT_LOCAL) {
            return false;
        }

        return true;
    }

    public function getForceAssetRefresh(): bool
    {
        return $this->get(self::BRISTOLIAN_ASSETS_FORCE_REFRESH);
    }

    public function getCommitSha(): string
    {
        return $this->get(self::BRISTOLIAN_COMMIT_SHA);
    }
}
