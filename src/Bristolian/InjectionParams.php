<?php

declare(strict_types = 1);

namespace Bristolian;

use DI\Injector;

class InjectionParams
{
    /**
     * @var array<string, mixed>
     */
    public array $shares;

    /**
     * @var array<string, mixed>
     */
    public array $aliases;

    /**
     * @var array<string, mixed>
     */
    public array $classParams;

    /**
     * @var array<string, mixed>
     */
    public array $delegates;

    /**
     * @var array<string, mixed>
     */
    public array $prepares;

    /**
     * @var array<string, mixed>
     */
    public array $namedParams;

    /**
     * @param array<string, mixed> $shares
     * @param array<string, mixed> $aliases
     * @param array<string, mixed> $delegates
     * @param array<string, mixed> $classParams
     * @param array<string, mixed> $prepares
     * @param array<string, mixed> $namedParams
     */
    public function __construct(
        array $shares = [],
        array $aliases = [],
        array $delegates = [],
        array $classParams = [],
        array $prepares = [],
        array $namedParams = []
    ) {
        $this->shares = $shares;
        $this->aliases = $aliases;
        $this->delegates = $delegates;
        $this->classParams = $classParams;
        $this->prepares = $prepares;
        $this->namedParams = $namedParams;
    }

    /**
     * @param mixed[] $params
     * @return self
     */
    public static function fromSharedObjects(array $params): self
    {
        $instance = new self();
        foreach ($params as $interface => $object) {
            $class = get_class($object);
            if (strcasecmp($class, $interface) !== 0) {
                //Avoid issues where the implementation is being shared
                //by class name rather than interface
                $instance->aliases[$interface] = $class;
            }
            $instance->shares[] = $object;
        }

        return $instance;
    }

    /**
     * @param string $original
     * @param string $alias
     * @return void
     */
    public function alias(string $original, string $alias): void
    {
        $this->aliases[$original] = $alias;
    }

    /**
     * @param class-string|object $classOrInstance
     * @return void
     */
    public function share(string|object $classOrInstance): void
    {
        $this->shares[] = $classOrInstance;
    }

    /**
     * @param string $paramName
     * @param mixed $value
     * @return void
     */
    public function defineNamedParam(string $paramName, mixed $value): void
    {
        $this->namedParams[$paramName] = $value;
    }

    /**
     * @param string $className
     * @param mixed $delegate
     * @return void
     */
    public function delegate(string $className, mixed $delegate): void
    {
        $this->delegates[$className] = $delegate;
    }

    /**
     * @param string $className
     * @param mixed[] $params
     * @return void
     */
    public function defineClassParam(string $className, array $params): void
    {
        $this->classParams[$className] = $params;
    }

    /**
     * @param string $className
     * @param mixed $prepareCallable
     * @return void
     */
    public function prepare(string $className, mixed $prepareCallable): void
    {
        $this->prepares[$className] = $prepareCallable;
    }

//    /**
//     * @param array $sharedObjects An array where the keys are the interface/classnames to
//     * be replaced, and the values are the new classes/objects to be used.
//     */
//    public function mergeSharedObjects(array $sharedObjects)
//    {
//        $newAliases = [];
//        foreach ($this->aliases as $interface => $implementation) {
//            if (array_key_exists($interface, $sharedObjects) === true) {
//                if (is_object($sharedObjects[$interface]) === false) {
//                    $message = sprintf(
//                        "Expected object but received type: %s",
//                        gettype($sharedObjects[$interface])
//                    );
//                    throw new \Auryn\InjectorException($message);
//                }
//                $newAliases[$interface] = get_class($sharedObjects[$interface]);
//                $this->shares[] = $sharedObjects[$interface];
//                unset($sharedObjects[$interface]);
//            }
//            else {
//                $newAliases[$interface] = $implementation;
//            }
//        }
//
//        $this->aliases = $newAliases;
//
//        foreach ($sharedObjects as $interface => $implementation) {
//            if (is_object($sharedObjects[$interface]) === false) {
//                $message = sprintf(
//                    "Expected object but received type: %s",
//                    gettype($sharedObjects[$interface])
//                );
//                throw new \Auryn\InjectorException($message);
//            }
//            $classname = get_class($implementation);
//            if (strcasecmp($classname, $interface) !== 0) {
//                //Avoid issues where the object is the same class
//                //as the alias
//                $this->aliases[$interface]  = $classname;
//            }
//            $this->shares[] = $sharedObjects[$interface];
//        }
//    }

    /**
     * @param Injector $injector
     */
    public function addToInjector(Injector $injector): void
    {
        foreach ($this->aliases as $original => $alias) {
            $injector->alias($original, $alias);
        }

        foreach ($this->shares as $share) {
            $injector->share($share);
        }
        
        foreach ($this->namedParams as $paramName => $value) {
            $injector->defineParam($paramName, $value);
        }

        foreach ($this->delegates as $className => $callable) {
            $injector->delegate($className, $callable);
        }

        foreach ($this->prepares as $className => $callable) {
            $injector->prepare($className, $callable);
        }

        foreach ($this->classParams as $className => $params) {
            $injector->define($className, $params);
        }
    }
}
