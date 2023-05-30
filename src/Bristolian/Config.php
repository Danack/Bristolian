<?php

namespace Bristolian;

use Bristolian\Data\DatabaseUserConfig;

class Config
{
    const BRISTOLIAN_ASSETS_FORCE_REFRESH = 'bristol_org.force_assets_refresh';
    const BRISTOLIAN_COMMIT_SHA = 'bristol_org.sha';
    const BRISTOLIAN_DEPLOY_TIME = 'bristol_org.deploy_time';
    const BRISTOLIAN_ENVIRONMENT = 'bristol_org.env';
    const BRISTOLIAN_REDIS_INFO = 'bristol_org.redis_info';

    const BRISTOLIAN_SQL_HOST     = 'bristol_org.db.host';
    const BRISTOLIAN_SQL_DATABASE = 'bristol_org.db.schema';
    const BRISTOLIAN_SQL_USERNAME = 'bristol_org.db.username';
    const BRISTOLIAN_SQL_PASSWORD = 'bristol_org.db.password';

    /**
     * @var array<string, int|string|bool|mixed>
     */
    private array $values = [];

    public function __construct()
    {
        $this->values = getGeneratedConfig();
    }

    /**
     * @param $key
     * @return int|string|bool|mixed[]
     * @throws \Exception
     */

    private function get(string $key): int|string|bool|array
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

    public function getRedisInfo(): \Bristolian\Config\RedisConfig
    {
        $data = $this->get(self::BRISTOLIAN_REDIS_INFO);

        return new \Bristolian\Config\RedisConfig(
            $data['host'],
            $data['password'],
            $data['port']
        );
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

    public function getDatabaseUserConfig(): DatabaseUserConfig
    {
        return new DatabaseUserConfig(
            $this->get(self::BRISTOLIAN_SQL_HOST),
            $this->get(self::BRISTOLIAN_SQL_USERNAME),
            $this->get(self::BRISTOLIAN_SQL_PASSWORD),
            $this->get(self::BRISTOLIAN_SQL_DATABASE)
        );
    }
}
