<?php

namespace Soli\Tests\Di;

use Soli\Tests\TestCase;
use Soli\Di\Container;
use Soli\Di\ContainerInterface;
use Soli\Di\ContainerAwareInterface;

class ContainerTest extends TestCase
{
    /**
     * @var \Soli\Di\ContainerInterface
     */
    protected $di;

    /**
     * @var string
     */
    protected $myComponent;

    public function setUp()
    {
        $this->di = new Container();
        $this->myComponent = __NAMESPACE__ . '\MyComponent';
    }

    public function testContainerInstance()
    {
        $this->assertInstanceOf('\Soli\Di\ContainerInterface', Container::instance());
    }

    public function testClosureInjection()
    {
        $this->di->set('closure', function () {
            return 'closure instance';
        });
        $service = $this->di->get('closure');

        $this->assertEquals('closure instance', $service);
    }

    public function testClosureWithParametersInjection()
    {
        $this->di->set('add', function ($a, $b) {
            return $a + $b;
        });
        $closureWithParameters = $this->di->get('add', [1, 2]);

        $this->assertEquals(3, $closureWithParameters);
    }

    public function testClassInjection()
    {
        // 清除上面测试用例中已经设置的 "someService" 服务，的共享实例
        $this->di->remove('someService');

        $this->di->set('someService', $this->myComponent);
        $service = $this->di->get('someService');

        $this->assertInstanceOf($this->myComponent, $service);
    }

    public function testClassWithParametersInjection()
    {
        // 清除上面测试用例中已经设置的 "someService" 服务，的共享实例
        $this->di->remove('someService');

        $this->di->set('someService', $this->myComponent);
        $service = $this->di->get('someService', [100]);

        $this->assertEquals(100, $service->getId());
    }

    public function testInstanceInjection()
    {
        $this->di->set('instance', new $this->myComponent);
        $service = $this->di->get('instance');

        $this->assertInstanceOf($this->myComponent, $service);
    }

    /**
     * 不支持注册数组
     *
     * @expectedException \Exception
     */
    public function testArrayInjection()
    {
        $array = [
            'aa' => 11,
            'bb' => 22,
        ];
        $this->di->set('array', $array);
        $service = $this->di->get('array');

        $this->assertEquals('22', $service['bb']);
    }

    public function testGetShared()
    {
        // 清除上面测试用例中已经设置的 "someService" 服务，的共享实例
        $this->di->remove('someService');

        $this->di->set('someService', $this->myComponent);

        // 获取一个新的实例
        $service1 = $this->di->get('someService');
        // 获取并实例化一个共享实例
        $service2 = $this->di->getShared('someService');
        // 获取一个共享实例
        $service3 = $this->di->getShared('someService');
        // 获取一个新的实例
        $service4 = $this->di->get('someService');

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
        $this->di->remove('someService');

        $this->di->setShared('someService', $this->myComponent);

        $service1 = $this->di->get('someService');
        $service2 = $this->di->get('someService');

        $service3 = $this->di->getShared('someService');

        $true12 = $service1 === $service2;
        $true23 = $service3 === $service2;
        $trueId13 = $service1->getId() === $service2->getId();

        $this->assertTrue($true12);
        $this->assertTrue($true23);
        $this->assertTrue($trueId13);
    }

    public function testArrayAccess()
    {
        $di = $this->di;

        // offsetSet
        $di['someService1'] = new \stdClass;
        $di->setShared('someService2', new \ArrayObject);

        $service1 = $di->get('someService1');
        // offsetGet
        $service2 = $di['someService2'];

        // offsetExists
        if (isset($di['someService2'])) {
            // offsetUnset
            unset($di['someService2']);
        }

        $this->assertInstanceOf('\stdClass', $service1);
        $this->assertInstanceOf('\ArrayObject', $service2);
    }

    public function testMagicGet()
    {
        $di = $this->di;

        $di['someService1'] = new \stdClass;
        $di->setShared('someService2', new \ArrayObject);

        $service1 = $di->someService1;
        $service2 = $di->someService2;

        $this->assertInstanceOf('\stdClass', $service1);
        $this->assertInstanceOf('\ArrayObject', $service2);
    }

    public function testGetServiceById()
    {
        $di = $this->di;

        $di->set('someService', new \stdClass);
        $service = $di->getService('someService');

        $this->assertInstanceOf('\Soli\Di\ServiceInterface', $service);
    }

    /**
     * @expectedException \Exception
     */
    public function testCantGetServiceById()
    {
        $service = $this->di->getService('notExistsService');
    }

    public function testGetServices()
    {
        $di = $this->di;

        $di->set('someService', new \stdClass);
        $services = $di->getServices();

        $service = array_shift($services);

        $this->assertInstanceOf('\Soli\Di\ServiceInterface', $service);
    }

    public function testGetClassName()
    {
        $service = $this->di->get($this->myComponent);

        $this->assertInstanceOf($this->myComponent, $service);
    }

    public function testContainerAware()
    {
        // 清除上面测试用例中已经设置的 "someService" 服务，的共享实例
        $this->di->remove('someService');

        $this->di->set('someService', $this->myComponent);
        $service = $this->di->get('someService');

        $this->assertInstanceOf('\Soli\Di\ContainerInterface', $service->getDi());
    }

    public function testClosureInjectionUseThis()
    {
        $this->di->set('closure', function () {
            return $this;
        });
        $service = $this->di->get('closure');

        $this->assertInstanceOf('\Soli\Di\ContainerInterface', $service);
    }

    public function testClosureInjectionUseThisCallOtherService()
    {
        $this->di->set('service1', function () {
            return 'service1 returned';
        });

        $this->di->set('closure', function () {
            return $this->get('service1');
        });
        $service = $this->di->get('closure');
        $this->assertEquals('service1 returned', $service);

        $this->di->set('closure', function () {
            return $this->service1;
        });
        $service = $this->di->get('closure');
        $this->assertEquals('service1 returned', $service);
    }

    /**
     * @expectedException \Exception
     */
    public function testCannotResolved()
    {
        $service = $this->di->get('notExistsService');
    }
}

class MyComponent implements ContainerAwareInterface
{
    protected $id;

    /**
     * @var \Soli\Di\ContainerInterface
     */
    protected $container;

    public function setDi(ContainerInterface $di)
    {
        $this->container = $di;
    }

    /**
     * @return \Soli\Di\ContainerInterface
     */
    public function getDi()
    {
        return $this->container;
    }

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
