<?php

namespace Bristolian;

class Config
{
    const BRISTOLIAN_ENVIRONMENT = 'bristol_org.env';

    const BRISTOLIAN_COMMIT_SHA = 'bristol_org.sha';
    const BRISTOLIAN_DEPLOY_TIME = 'bristol_org.deploy_time';
    const BRISTOLIAN_ASSETS_FORCE_REFRESH = 'bristol_org.force_assets_refresh';
    const BRISTOLIAN_REDIS_INFO = 'bristol_org.redis_info';

    /**
     * @param $key
     * @return mixed
     * @throws \Exception
     */
    public static function get($key)
    {
        static $values = null;
        if ($values === null) {
            $values = getGeneratedConfig();
        }

        if (array_key_exists($key, $values) == false) {
            throw new \Exception("No value for " . $key);
        }

        return $values[$key];
    }

    public static function testValuesArePresent(): void
    {
        $rc = new \ReflectionClass(self::class);
        $constants = $rc->getConstants();

        foreach ($constants as $constant) {
            $value = self::get($constant);
        }
    }



    public static function getVersion(): string
    {
        return self::get(self::IMAGICKDEMOS_ENVIRONMENT) . "_" . self::get(self::IMAGICKDEMOS_COMMIT_SHA);
    }

    public static function getDeployTime(): string
    {
        return self::get(self::IMAGICKDEMOS_DEPLOY_TIME);
    }

    public static function getEnvironment(): string
    {
        return self::get(self::IMAGICKDEMOS_ENVIRONMENT);
    }

    public static function isProductionEnv(): bool
    {
        if (self::getEnvironment() === App::ENVIRONMENT_LOCAL) {
            return false;
        }

        return true;
    }


//    public function __construct()
//    {
////        require_once __DIR__."/../../../clavis.php";
////        require_once __DIR__ . "/../../config.php";
//
//        $this->values = [];
//        $this->values = array_merge($this->values, getAppOptions());
////        $this->values = array_merge($this->values, getAppKeys());
//    }
//
//    public function getKey($key)
//    {
//        if (array_key_exists($key, $this->values) == false) {
//            throw new \Exception("Missing config value of $key");
//        }
//
//        return $this->values[$key];
//    }
//
////    private function getKeyWithDefault($key, $default)
////    {
////        if (array_key_exists($key, $this->values) === false) {
////            return $default;
////        }
////
////        return $this->values[$key];
////    }
//
//    public function getRedisPassword()
//    {
//        return $this->getKey(Config::REDIS_PASSWORD);
//    }
//
//    public function getJigCompileCheck()
//    {
//        return $this->getKey(self::JIG_COMPILE_CHECK);
//    }
//
//    public function getCachingSetting()
//    {
//        return $this->getKey(Config::CACHING_SETTING);
//    }
//
//    public function isProductionEnv()
//    {
//        if ($this->getEnvironment() === self::ENVIRONMENT_LOCAL) {
//            return false;
//        }
//
//        return true;
//    }
//
//    public function useSsl()
//    {
//        if ($this->getEnvironment() !== self::ENVIRONMENT_LOCAL) {
//            return true;
//        }
//        return false;
//    }
//
//
//    public function getEnvironment()
//    {
//        return $this->getKey(self::ENVIRONMENT);
//    }
}
