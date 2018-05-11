<?php

namespace Soli\Tests\Di;

use PHPUnit\Framework\TestCase;

use Soli\Di\Container;
use Soli\Di\Service;
use Soli\Tests\Data\Di\CanNotInstantiable;
use Soli\Tests\Data\Di\NoConstructor;
use Soli\Tests\Data\Di\UnresolvableDependency;
use Soli\Tests\Data\Di\UnresolvableDependency2;

class ServiceTest extends TestCase
{
    public function testNonShared()
    {
        $service = new Service('someName', function () {
            return new \stdClass;
        }, false);

        $c1 = $service->resolve();
        $c2 = $service->resolve();

        $this->assertFalse($c1 === $c2);
    }

    public function testShared()
    {
        $service = new Service('someName', function () {
            return new \stdClass;
        }, true);

        $c1 = $service->resolve();
        $c2 = $service->resolve();

        $this->assertTrue($c1 === $c2);
    }

    public function testResolveObjectInstance()
    {
        $service = new Service('someName', new \stdClass);

        $a = $service->resolve();

        $this->assertInstanceOf('\stdClass', $a);
    }

    public function testResolveClassWithParameters()
    {
        $service = new Service('someName', 'ReflectionFunction');

        $parameters = ['name' => 'substr'];
        $a = $service->resolve($parameters);

        $this->assertEquals('substr', $a->name);
    }

    public function testResolveClosureWithParameters()
    {
        $service = new Service('someName', function ($a, $b) {
            return $a + $b;
        });

        $parameters = [1, 2];
        $sum = $service->resolve($parameters);

        $this->assertEquals(3, $sum);
    }

    public function testIsShared()
    {
        $service = new Service('someName', function () {
            return new \stdClass;
        }, false);

        $shared = $service->isShared();

        $this->assertFalse($shared);
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessageRegExp /Service '.+' cannot be resolved/
     */
    public function testCannotResolved()
    {
        $service = new Service('notExistsClass', 'not_exists_class_name');

        $service->resolve();
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessageRegExp /Service '.+' cannot be resolved/
     */
    public function testResolveCannotCase()
    {
        $service = new Service('cannotCase', null);

        $service->resolve();
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessageRegExp /Can not instantiate .+/
     */
    public function testResolveCannotInstantiate()
    {
        $service = new Service(CanNotInstantiable::class, CanNotInstantiable::class);

        $service->resolve();
    }

    public function testResolveNewInstanceWithoutArgs()
    {
        $service = new Service(NoConstructor::class, NoConstructor::class);

        $instance = $service->resolve();

        $this->assertInstanceOf(NoConstructor::class, $instance);
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessageRegExp /Unresolvable dependency resolving .+/
     */
    public function testResolveUnresolvableDependencyPrimitive()
    {
        $service = new Service(UnresolvableDependency::class, UnresolvableDependency::class);

        $service->resolve();
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessageRegExp /Unresolvable dependency resolving .+/
     */
    public function testResolveUnresolvableDependencyClass()
    {
        $service = new Service(UnresolvableDependency::class, UnresolvableDependency::class);

        $parameters = ['default' => 'yes'];
        $container = new Container();

        $service->resolve($parameters, $container);
    }

    public function testResolveUnresolvableDependencyClass2OptionalParameter()
    {
        $service = new Service(UnresolvableDependency2::class, UnresolvableDependency2::class);

        $parameters = ['default' => 'yes'];
        $container = new Container();

        $instance = $service->resolve($parameters, $container);

        $this->assertInstanceOf(UnresolvableDependency2::class, $instance);
    }
}
