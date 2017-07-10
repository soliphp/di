<?php

namespace Soli\Tests\Di;

use Soli\Tests\TestCase;
use Soli\Di\Container;
use Soli\Di\ContainerInterface;
use Soli\Di\ContainerAwareInterface;
use Soli\Di\ContainerAwareTrait;

class ContainerTest extends TestCase
{
    /**
     * @var \Soli\Di\ContainerInterface
     */
    protected $container;

    /**
     * @var string
     */
    protected $myComponent;

    public function setUp()
    {
        $this->container = new Container();
        $this->myComponent = __NAMESPACE__ . '\MyComponent';
    }

    public function testContainerInstance()
    {
        $this->assertInstanceOf('\Soli\Di\ContainerInterface', Container::instance());
    }

    public function testClosureInjection()
    {
        $this->container->set('closure', function () {
            return 'closure instance';
        });
        $service = $this->container->get('closure');

        $this->assertEquals('closure instance', $service);
    }

    public function testClosureWithParametersInjection()
    {
        $this->container->set('add', function ($a, $b) {
            return $a + $b;
        });
        $closureWithParameters = $this->container->get('add', [1, 2]);

        $this->assertEquals(3, $closureWithParameters);
    }

    public function testClassInjection()
    {
        // 清除上面测试用例中已经设置的 "someService" 服务，的共享实例
        $this->container->remove('someService');

        $this->container->set('someService', $this->myComponent);
        $service = $this->container->get('someService');

        $this->assertInstanceOf($this->myComponent, $service);
    }

    public function testClassWithParametersInjection()
    {
        // 清除上面测试用例中已经设置的 "someService" 服务，的共享实例
        $this->container->remove('someService');

        $this->container->set('someService', $this->myComponent);
        $service = $this->container->get('someService', [100]);

        $this->assertEquals(100, $service->getId());
    }

    public function testInstanceInjection()
    {
        $this->container->set('instance', new $this->myComponent);
        $service = $this->container->get('instance');

        $this->assertInstanceOf($this->myComponent, $service);
    }

    public function testArrayInjection()
    {
        $array = [
            'aa' => 11,
            'bb' => 22,
        ];
        $this->container->set('array', $array);
        $service = $this->container->get('array');

        $this->assertEquals('22', $service['bb']);
    }

    public function testGetShared()
    {
        // 清除上面测试用例中已经设置的 "someService" 服务，的共享实例
        $this->container->remove('someService');

        $this->container->set('someService', $this->myComponent);

        // 获取一个新的实例
        $service1 = $this->container->get('someService');
        // 获取并实例化一个共享实例
        $service2 = $this->container->getShared('someService');
        // 获取一个共享实例
        $service3 = $this->container->getShared('someService');
        // 获取一个新的实例
        $service4 = $this->container->get('someService');

        $false12 = $service1 ==  $service2;
        $true32  = $service3 === $service2;
        $false34 = $service3 === $service4;

        $this->assertFalse($false12);
        $this->assertTrue($true32);
        $this->assertFalse($false34);
    }

    public function testSetShared()
    {
        // 清除上面测试用例中已经设置的 "someService" 服务，的共享实例
        $this->container->remove('someService');

        $this->container->setShared('someService', $this->myComponent);

        $service1 = $this->container->get('someService');
        $service2 = $this->container->get('someService');

        $service3 = $this->container->getShared('someService');

        $true12 = $service1 === $service2;
        $true23 = $service3 === $service2;
        $trueId13 = $service1->getId() === $service2->getId();

        $this->assertTrue($true12);
        $this->assertTrue($true23);
        $this->assertTrue($trueId13);
    }

    public function testArrayAccess()
    {
        $container = $this->container;

        // offsetSet
        $container['someService1'] = new \stdClass;
        $container->setShared('someService2', new \ArrayObject);

        $service1 = $container->get('someService1');
        // offsetGet
        $service2 = $container['someService2'];

        // offsetExists
        if (isset($container['someService2'])) {
            // offsetUnset
            unset($container['someService2']);
        }

        $this->assertInstanceOf('\stdClass', $service1);
        $this->assertInstanceOf('\ArrayObject', $service2);
    }

    public function testMagicGet()
    {
        $container = $this->container;

        $container['someService1'] = new \stdClass;
        $container->setShared('someService2', new \ArrayObject);

        $service1 = $container->someService1;
        $service2 = $container->someService2;

        $this->assertInstanceOf('\stdClass', $service1);
        $this->assertInstanceOf('\ArrayObject', $service2);
    }

    public function testGetServiceById()
    {
        $container = $this->container;

        $container->set('someService', new \stdClass);
        $service = $container->getService('someService');

        $this->assertInstanceOf('\Soli\Di\ServiceInterface', $service);
    }

    /**
     * @expectedException \Exception
     */
    public function testCantGetServiceById()
    {
        $service = $this->container->getService('notExistsService');
    }

    public function testGetServices()
    {
        $container = $this->container;

        $container->set('someService', new \stdClass);
        $services = $container->getServices();

        $service = array_shift($services);

        $this->assertInstanceOf('\Soli\Di\ServiceInterface', $service);
    }

    public function testGetClassName()
    {
        $service = $this->container->get($this->myComponent);

        $this->assertInstanceOf($this->myComponent, $service);
    }

    public function testContainerAware()
    {
        // 清除上面测试用例中已经设置的 "someService" 服务，的共享实例
        $this->container->remove('someService');

        $this->container->set('someService', $this->myComponent);
        $service = $this->container->get('someService');

        $this->assertInstanceOf('\Soli\Di\ContainerInterface', $service->getContainer());
    }

    public function testClosureInjectionUseThis()
    {
        $this->container->set('closure', function () {
            return $this;
        });
        $service = $this->container->get('closure');

        $this->assertInstanceOf('\Soli\Di\ContainerInterface', $service);
    }

    public function testClosureInjectionUseThisCallOtherService()
    {
        $this->container->set('service1', function () {
            return 'service1 returned';
        });

        $this->container->set('closure', function () {
            return $this->get('service1');
        });
        $service = $this->container->get('closure');
        $this->assertEquals('service1 returned', $service);

        $this->container->set('closure', function () {
            return $this->service1;
        });
        $service = $this->container->get('closure');
        $this->assertEquals('service1 returned', $service);
    }

    /**
     * @expectedException \Exception
     */
    public function testCannotResolved()
    {
        $service = $this->container->get('notExistsService');
    }
}

class MyComponent implements ContainerAwareInterface
{
    use ContainerAwareTrait;

    protected $id;

    public function __construct($id = 0)
    {
        if ($id) {
            $this->id = $id;
        } else {
            $this->id = microtime(true) . mt_rand();
        }
    }

    public function getId()
    {
        return $this->id;
    }
}
