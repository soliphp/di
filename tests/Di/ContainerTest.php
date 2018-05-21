<?php

namespace Soli\Tests\Di;

use PHPUnit\Framework\TestCase;

use Soli\Di\Container;
use Soli\Di\ContainerInterface;
use Soli\Di\ServiceInterface;

use Soli\Tests\Data\Di\MyComponent;
use Soli\Tests\Data\Di\A;
use Soli\Tests\Data\Di\B;
use Soli\Tests\Data\Di\C;

use stdClass;

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

    public function testClassTypeHintAutoInjection()
    {
        // 清除上面测试用例中已经设置的 "someService" 服务，的共享实例
        $this->container->remove('someService');

        $this->container->set('someService', MyComponent::class);
        /** @var MyComponent $service */
        $service = $this->container->get('someService');

        $this->assertInstanceOf(MyComponent::class, $service);
        $this->assertInstanceOf(A::class, $service->a);
        $this->assertInstanceOf(B::class, $service->a->b);
        $this->assertInstanceOf(C::class, $service->a->c);
    }

    public function testClassWithParametersInjection()
    {
        // 清除上面测试用例中已经设置的 "someService" 服务，的共享实例
        $this->container->remove('someService');

        $this->container->set('someService', MyComponent::class);
        $service = $this->container->get('someService', ['id' => 100]);

        $this->assertEquals(100, $service->getId());
    }

    public function testInstanceInjection()
    {
        $this->container->set('instance', new stdClass());
        $service = $this->container->get('instance');

        $this->assertInstanceOf(stdClass::class, $service);
    }

    public function testSetShared()
    {
        // 清除上面测试用例中已经设置的 "someService" 服务，的共享实例
        $this->container->remove('someService');

        $this->container->set('someService', MyComponent::class, true);

        $service1 = $this->container->get('someService');
        $service2 = $this->container->get('someService');

        $true12 = $service1 === $service2;
        $trueId12 = $service1->getId() === $service2->getId();

        $this->assertTrue($true12);
        $this->assertTrue($trueId12);
    }

    public function testArrayAccess()
    {
        $container = $this->container;

        // offsetSet
        $container['someService1'] = new \stdClass();
        $container->set('someService2', new \ArrayObject());

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
        $container->set('someService2', new \ArrayObject());

        $service1 = $container->someService1;
        $service2 = $container->someService2;

        $this->assertInstanceOf('stdClass', $service1);
        $this->assertInstanceOf('ArrayObject', $service2);
    }

    public function testClear()
    {
        $container = $this->container;
        $container->set('someService', new \stdClass());

        $has = $container->has('someService');
        $this->assertTrue($has);

        $container->clear();

        $has = $container->has('someService');
        $this->assertFalse($has);
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

    public function testAlias()
    {
        $aliases = [
            'app' => [
                \Soli\Application::class,
            ],
            'container' => [
                \Soli\Di\Container::class,
                \Psr\Container\ContainerInterface::class,
            ],
            'one' => [
                'two',
            ],
            'two' => [
                'three',
            ],
        ];

        $container = $this->container;
        foreach ($aliases as $key => $aliases) {
            foreach ($aliases as $alias) {
                $container->alias($alias, $key);
            }
        }

        $containerAlias = $container->getAliasId(\Psr\Container\ContainerInterface::class);
        $oneAlias = $container->getAliasId('three');

        $this->assertEquals('container', $containerAlias);
        $this->assertEquals('one', $oneAlias);
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessageRegExp /.+ is aliased to itself./
     */
    public function testAliasedItselfException()
    {
        $selfAlias = 'self_alias';

        $container = $this->container;

        $container->alias($selfAlias, $selfAlias);

        $container->getAliasId($selfAlias);
    }
}
