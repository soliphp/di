<?php

namespace Soli\Tests\Di;

use Soli\Tests\TestCase;

use Soli\Di\Service;
use Soli\Tests\Data\Di\CanNotInstantiable;
use Soli\Tests\Data\Di\NoConstructor;
use Soli\Tests\Data\Di\UnresolvableDependency;
use Soli\Tests\Data\Di\UnresolvableDependency2;

class ServiceTest extends TestCase
{
    public function testResolveObjectInstance(): void
    {
        $service = new Service('someName', new \stdClass());

        $a = $service->resolve();

        $this->assertInstanceOf('\stdClass', $a);
    }

    public function testResolveClassWithParameters(): void
    {
        $service = new Service('someName', 'ReflectionFunction');

        $parameters = ['function' => 'substr'];
        $a = $service->resolve($parameters);

        $this->assertEquals('substr', $a->name);
    }

    public function testResolveClosureWithParameters(): void
    {
        $service = new Service('someName', function ($a, $b) {
            return $a + $b;
        });

        $parameters = [
            'a' => 1,
            'b' => 2,
        ];
        $sum = $service->resolve($parameters);

        $this->assertEquals(3, $sum);
    }

    public function testIsShared(): void
    {
        $sharedService = new Service('sharedService', function () {
            return new \stdClass();
        }, true);

        $nonSharedService = new Service('nonSharedService', function () {
            return new \stdClass();
        }, false);

        $this->assertTrue($sharedService->isShared());
        $this->assertFalse($nonSharedService->isShared());
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessageRegExp /Service '.+' cannot be resolved/
     */
    public function testCannotResolved(): void
    {
        $this->expectException(\DomainException::class);
        $this->expectExceptionMessageMatches("/Service '.+' cannot be resolved/");

        $service = new Service('notExistsClass', 'not_exists_class_name');

        $service->resolve();
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessageRegExp /Service '.+' cannot be resolved/
     */
    public function testResolveCannotCase(): void
    {
        $this->expectException(\DomainException::class);
        $this->expectExceptionMessageMatches("/Service '.+' cannot be resolved/");

        $service = new Service('cannotCase', null);

        $service->resolve();
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessageRegExp /Can not instantiate .+/
     */
    public function testResolveCannotInstantiate(): void
    {
        $this->expectException(\DomainException::class);
        $this->expectExceptionMessageMatches("/Can not instantiate .+/");

        $service = new Service(CanNotInstantiable::class, CanNotInstantiable::class);

        $service->resolve();
    }

    public function testResolveNewInstanceWithoutArgs(): void
    {
        $service = new Service(NoConstructor::class, NoConstructor::class);

        $instance = $service->resolve();

        $this->assertInstanceOf(NoConstructor::class, $instance);
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessageRegExp /Unresolvable dependency resolving .+/
     */
    public function testResolveUnresolvableDependencyPrimitive(): void
    {
        $this->expectException(\DomainException::class);
        $this->expectExceptionMessageMatches("/Unresolvable dependency resolving .+/");

        $service = new Service(UnresolvableDependency::class, UnresolvableDependency::class);

        $service->resolve();
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessageRegExp /Unresolvable dependency resolving .+/
     */
    public function testResolveUnresolvableDependencyClass(): void
    {
        $this->expectException(\DomainException::class);
        $this->expectExceptionMessageMatches("/Unresolvable dependency resolving .+/");

        $service = new Service(UnresolvableDependency::class, UnresolvableDependency::class);

        $parameters = ['default' => 'yes'];

        $service->resolve($parameters, static::$container);
    }

    public function testResolveUnresolvableDependencyClass2OptionalParameter(): void
    {
        $service = new Service(UnresolvableDependency2::class, UnresolvableDependency2::class);

        $parameters = ['default' => 'yes'];

        $instance = $service->resolve($parameters, static::$container);

        $this->assertInstanceOf(UnresolvableDependency2::class, $instance);
    }
}
