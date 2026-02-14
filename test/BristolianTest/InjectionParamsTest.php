<?php

namespace BristolianTest;

use Bristolian\InjectionParams;
use BristolianTest\BaseTestCase;
use DI\Injector;

/**
 * @coversNothing
 */
class InjectionParamsTest extends BaseTestCase
{
    /**
     * @covers \Bristolian\InjectionParams
     */
    public function testWorks_constructor_with_defaults()
    {
        $params = new InjectionParams();

        $this->assertInstanceOf(InjectionParams::class, $params);
        $this->assertSame([], $params->shares);
        $this->assertSame([], $params->aliases);
        $this->assertSame([], $params->delegates);
        $this->assertSame([], $params->classParams);
        $this->assertSame([], $params->prepares);
        $this->assertSame([], $params->namedParams);
    }

    /**
     * @covers \Bristolian\InjectionParams
     */
    public function testWorks_constructor_with_all_parameters()
    {
        $shares = [new \stdClass()];
        $aliases = ['Interface' => 'Implementation'];
        $delegates = ['Class' => 'callable'];
        $classParams = ['Class' => ['param' => 'value']];
        $prepares = ['Class' => 'callable'];
        $namedParams = ['paramName' => 'value'];

        $params = new InjectionParams(
            $shares,
            $aliases,
            $delegates,
            $classParams,
            $prepares,
            $namedParams
        );

        $this->assertSame($shares, $params->shares);
        $this->assertSame($aliases, $params->aliases);
        $this->assertSame($delegates, $params->delegates);
        $this->assertSame($classParams, $params->classParams);
        $this->assertSame($prepares, $params->prepares);
        $this->assertSame($namedParams, $params->namedParams);
    }

    /**
     * @covers \Bristolian\InjectionParams
     */
    public function testWorks_fromSharedObjects_with_interface()
    {
        $interface = 'TestInterface';
        $object = new \stdClass();

        $params = InjectionParams::fromSharedObjects([$interface => $object]);

        $this->assertInstanceOf(InjectionParams::class, $params);
        $this->assertCount(1, $params->shares);
        $this->assertSame($object, $params->shares[0]);
        $this->assertArrayHasKey($interface, $params->aliases);
        $this->assertSame(\stdClass::class, $params->aliases[$interface]);
    }

    /**
     * @covers \Bristolian\InjectionParams
     */
    public function testWorks_fromSharedObjects_with_same_class()
    {
        $className = \stdClass::class;
        $object = new \stdClass();

        $params = InjectionParams::fromSharedObjects([$className => $object]);

        $this->assertInstanceOf(InjectionParams::class, $params);
        $this->assertCount(1, $params->shares);
        $this->assertSame($object, $params->shares[0]);
        $this->assertArrayNotHasKey($className, $params->aliases);
    }

    /**
     * @covers \Bristolian\InjectionParams
     */
    public function testWorks_alias()
    {
        $params = new InjectionParams();
        $params->alias('Original', 'Alias');

        $this->assertArrayHasKey('Original', $params->aliases);
        $this->assertSame('Alias', $params->aliases['Original']);
    }

    /**
     * @covers \Bristolian\InjectionParams
     */
    public function testWorks_share_with_string()
    {
        $params = new InjectionParams();
        $params->share(\stdClass::class);

        $this->assertCount(1, $params->shares);
        $this->assertSame(\stdClass::class, $params->shares[0]);
    }

    /**
     * @covers \Bristolian\InjectionParams
     */
    public function testWorks_share_with_object()
    {
        $params = new InjectionParams();
        $object = new \stdClass();
        $params->share($object);

        $this->assertCount(1, $params->shares);
        $this->assertSame($object, $params->shares[0]);
    }

    /**
     * @covers \Bristolian\InjectionParams
     */
    public function testWorks_defineNamedParam()
    {
        $params = new InjectionParams();
        $params->defineNamedParam('paramName', 'value');

        $this->assertArrayHasKey('paramName', $params->namedParams);
        $this->assertSame('value', $params->namedParams['paramName']);
    }

    /**
     * @covers \Bristolian\InjectionParams
     */
    public function testWorks_delegate()
    {
        $params = new InjectionParams();
        $callable = function () {
            return new \stdClass();
        };
        $params->delegate('ClassName', $callable);

        $this->assertArrayHasKey('ClassName', $params->delegates);
        $this->assertSame($callable, $params->delegates['ClassName']);
    }

    /**
     * @covers \Bristolian\InjectionParams
     */
    public function testWorks_defineClassParam()
    {
        $params = new InjectionParams();
        $classParams = ['param' => 'value'];
        $params->defineClassParam('ClassName', $classParams);

        $this->assertArrayHasKey('ClassName', $params->classParams);
        $this->assertSame($classParams, $params->classParams['ClassName']);
    }

    /**
     * @covers \Bristolian\InjectionParams
     */
    public function testWorks_prepare()
    {
        $params = new InjectionParams();
        $callable = function ($instance) {
            return $instance;
        };
        $params->prepare('ClassName', $callable);

        $this->assertArrayHasKey('ClassName', $params->prepares);
        $this->assertSame($callable, $params->prepares['ClassName']);
    }

    /**
     * @covers \Bristolian\InjectionParams
     */
    public function testWorks_addToInjector()
    {
        $injector = new Injector();
        $params = new InjectionParams();

        $object = new \stdClass();
        $params->alias('TestInterface', \stdClass::class);
        $params->share($object);
        $params->defineNamedParam('testParam', 'testValue');
        $params->delegate('TestClass', function () {
            return new \stdClass();
        });
        $params->prepare('TestClass', function ($instance) {
            return $instance;
        });
        $params->defineClassParam('TestClass', ['param' => 'value']);

        // This should not throw an exception
        $params->addToInjector($injector);

        // Verify the injector was configured - if we get here, no exception was thrown
        $this->assertInstanceOf(Injector::class, $injector);
    }
}
