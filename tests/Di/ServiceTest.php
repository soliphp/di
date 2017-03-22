<?php

namespace Soli\Tests\Di;

use Soli\Tests\TestCase;
use Soli\Di\Service;

class ServiceTest extends TestCase
{
    public function testNonShared()
    {
        $service = new Service('some_name', function () {
            return new \stdClass;
        }, false);

        $c1 = $service->resolve();
        $c2 = $service->resolve();

        $this->assertFalse($c1 === $c2);
    }

    public function testShared()
    {
        $service = new Service('some_name', function () {
            return new \stdClass;
        }, true);

        $c1 = $service->resolve();
        $c2 = $service->resolve();

        $this->assertTrue($c1 === $c2);
    }

    public function testResolveObjectInstance()
    {
        $service = new Service('some_name', new \stdClass);

        $a = $service->resolve();

        $this->assertInstanceOf('\stdClass', $a);
    }

    public function testResolveArray()
    {
        $arr = [1, 2];
        $service = new Service('some_name', $arr);

        $a = $service->resolve();

        $this->assertEquals($arr, $a);
    }

    public function testResolveClassWithParameters()
    {
        $service = new Service('some_name', 'ReflectionFunction');

        $parameters = ['substr'];
        $a = $service->resolve($parameters);

        $this->assertEquals('substr', $a->name);
    }

    public function testResolveClosureWithParameters()
    {
        $service = new Service('some_name', function ($a, $b) {
            return $a + $b;
        });

        $parameters = [1, 2];
        $sum = $service->resolve($parameters);

        $this->assertEquals(3, $sum);
    }

    /**
     * @expectedException \Exception
     */
    public function testCannotResolved()
    {
        $service = new Service('not_exists_class', 'not_exists_class_name');

        $service->resolve();
    }
}
