<?php

namespace Soli\Tests\Di;

use PHPUnit\Framework\TestCase;

use Soli\Di\Service;

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

    public function testResolveArray()
    {
        $arr = [1, 2];
        $service = new Service('someName', $arr);

        $a = $service->resolve();

        $this->assertEquals($arr, $a);
    }

    public function testResolveClassWithParameters()
    {
        $service = new Service('someName', 'ReflectionFunction');

        $parameters = ['substr'];
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
     */
    public function testCannotResolved()
    {
        $service = new Service('notExistsClass', 'not_exists_class_name');

        $service->resolve();
    }

    /**
     * @expectedException \Exception
     */
    public function testResolvedCannotCase()
    {
        $service = new Service('cannotCase', null);

        $service->resolve();
    }
}
