<?php

namespace Soli\Tests\Di;

use PHPUnit\Framework\TestCase;

use Soli\Di\Container;
use Soli\Di\ContainerInterface;
use Soli\Di\ServiceInterface;

use Soli\Tests\Data\Di\MyComponent;

class ContainerTest extends TestCase
{
    /**
     * @var \Soli\Di\ContainerInterface
     */
    protected $container;

    public function setUp()
    {
        $this->container = new Container();
    }

    public function testContainerInstance()
    {
        $this->assertInstanceOf(ContainerInterface::class, Container::instance());
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

        $this->container->set('someService', MyComponent::class);
        $service = $this->container->get('someService');

        $this->assertInstanceOf(MyComponent::class, $service);
    }

    public function testClassWithParametersInjection()
    {
        // 清除上面测试用例中已经设置的 "someService" 服务，的共享实例
        $this->container->remove('someService');

        $this->container->set('someService', MyComponent::class);
        $service = $this->container->get('someService', [100]);

        $this->assertEquals(100, $service->getId());
    }

    public function testInstanceInjection()
    {
        $this->container->set('instance', new MyComponent());
        $service = $this->container->get('instance');

        $this->assertInstanceOf(MyComponent::class, $service);
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

        $this->container->set('someService', MyComponent::class);

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

        $this->container->setShared('someService', MyComponent::class);

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
        $container['someService1'] = new \stdClass();
        $container->setShared('someService2', new \ArrayObject());

        $service1 = $container->get('someService1');
        // offsetGet
        $service2 = $container['someService2'];

        // offsetExists
        if (isset($container['someService2'])) {
            // offsetUnset
            unset($container['someService2']);
        }

        $this->assertInstanceOf('stdClass', $service1);
        $this->assertInstanceOf('ArrayObject', $service2);
    }

    public function testMagicGet()
    {
        /** @var \Soli\Di\Container $container */
        $container = $this->container;

        $container['someService1'] = new \stdClass();
        $container->setShared('someService2', new \ArrayObject());

        $service1 = $container->someService1;
        $service2 = $container->someService2;

        $this->assertInstanceOf('stdClass', $service1);
        $this->assertInstanceOf('ArrayObject', $service2);
    }

    public function testGetServiceById()
    {
        $container = $this->container;

        $container->set('someService', new \stdClass());
        $service = $container->getService('someService');

        $this->assertInstanceOf(ServiceInterface::class, $service);
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessageRegExp /Service '.+' wasn't found in the dependency injection container/
     */
    public function testCantGetServiceById()
    {
        $this->container->getService('notExistsService');
    }

    public function testGetServices()
    {
        $container = $this->container;

        $container->set('someService', new \stdClass());
        $services = $container->getServices();

        $service = array_shift($services);

        $this->assertInstanceOf(ServiceInterface::class, $service);
    }

    public function testClear()
    {
        $container = $this->container;

        $services = $container->getServices();
        $this->assertNotEmpty($services);

        $container->clear();

        $services = $container->getServices();
        $this->assertEmpty($services);
    }

    public function testGetClassName()
    {
        $service = $this->container->get(MyComponent::class);

        $this->assertInstanceOf(MyComponent::class, $service);
    }

    public function testInterfaceVsClass()
    {
        $this->container->set(ContainerInterface::class, Container::class);
        $container = $this->container->get(ContainerInterface::class);

        $this->assertInstanceOf(ContainerInterface::class, $container);
    }

    public function testContainerAware()
    {
        // 清除上面测试用例中已经设置的 "someService" 服务，的共享实例
        $this->container->remove('someService');

        $this->container->set('someService', MyComponent::class);
        $service = $this->container->get('someService');

        $this->assertInstanceOf(ContainerInterface::class, $service->getContainer());
    }

    public function testClosureInjectionUseThis()
    {
        $this->container->set('closure', function () {
            return $this;
        });
        $service = $this->container->get('closure');

        $this->assertInstanceOf(ContainerInterface::class, $service);
    }

    public function testClosureInjectionUseThisCallOtherService()
    {
        $this->container->set('service1', function () {
            return 'service1 returned';
        });

        $this->container->set('closure', function () {
            /** @var \Soli\Di\ContainerInterface $this */
            return $this->get('service1');
        });
        $service = $this->container->get('closure');
        $this->assertEquals('service1 returned', $service);

        $this->container->set('closure', function () {
            /** @var \Soli\Di\Container $this */
            return $this->service1;
        });
        $service = $this->container->get('closure');
        $this->assertEquals('service1 returned', $service);
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessageRegExp /Service '.+' wasn't found in the dependency injection container/
     */
    public function testCannotResolved()
    {
        $this->container->get('notExistsService');
    }
}
