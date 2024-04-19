<?php
/**
 * @author ueaner <ueaner@gmail.com>
 */

namespace Soli;

use Soli\Di\Container;
use Soli\Di\ContainerInterface;
use Soli\Di\ContainerAwareInterface;

/**
 * 组件基类
 *
 * 通过 $this->{serviceName} 访问属性的方式访问所有注册到容器中的服务
 *
 * @property \Soli\Di\ContainerInterface $container
 */
class Component implements ContainerAwareInterface
{
    /**
     * @var \Soli\Di\ContainerInterface
     */
    protected $diContainer;

    public function setContainer(ContainerInterface $diContainer): void
    {
        $this->diContainer = $diContainer;
    }

    /**
     * @return \Soli\Di\ContainerInterface
     */
    public function getContainer(): ContainerInterface
    {
        if ($this->diContainer === null) {
            $this->diContainer = Container::instance() ?: new Container();
        }
        return $this->diContainer;
    }

    /**
     * 获取容器本身，或者获取容器中的某个服务
     *
     * @param string $name
     * @return \Soli\Di\ContainerInterface|mixed
     */
    public function __get($name): mixed
    {
        $container = $this->getContainer();

        if ($container->has($name)) {
            $service = $container->get($name);
            // 将找到的服务添加到属性, 以便下次直接调用
            $this->$name = $service;
            return $service;
        }

        if ($name == 'container') {
            $this->container = $container;
            return $container;
        }

        trigger_error("Access to undefined property $name");
        return null;
    }
}
