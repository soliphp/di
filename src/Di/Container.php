<?php
/**
 * @author ueaner <ueaner@gmail.com>
 */
namespace Soli\Di;

use Psr\Container\ContainerInterface;

/**
 * 依赖注入容器
 *
 * 依赖注入容器的目的为了降低代码的耦合度，提高应用的可维护性。
 * 把组件之间的依赖，转换为对容器的依赖，通过容器
 * 进行服务管理(创建、配置和定位)。
 */
class Container implements ContainerInterface, \ArrayAccess
{
    /**
     * Container 实例
     *
     * @var \Soli\Di\Container
     */
    public static $instance;

    /**
     * services 服务容器
     *
     * @var array
     */
    protected static $services = [];

    /**
     * shared 服务实例
     *
     * @var array
     */
    protected static $sharedInstances = [];

    /**
     * 初始化 Container 默认实例
     */
    public function __construct()
    {
        if (static::$instance === null) {
            static::$instance = $this;
        }
        return static::$instance;
    }

    /**
     * 获取 Container 实例
     */
    public static function instance()
    {
        return static::$instance;
    }

    /**
     * 注册一个服务到容器
     *
     * @param string $name
     * @param mixed $definition 服务定义, 类名|对象(实例化后的对象或Closure)|数组
     * @param bool $shared 为 true 则注册单例服务
     * @return Service
     */
    public function set($name, $definition, $shared = false)
    {
        $service = new Service($name, $definition, $shared);
        static::$services[$name] = $service;
        return $service;
    }

    /**
     * 注册单例服务
     *
     * @param string $name
     * @param mixed $definition 服务定义
     * @return Service
     */
    public function setShared($name, $definition)
    {
        return $this->set($name, $definition, true);
    }

    /**
     * 从容器中获取一个服务, 解析服务定义
     * 如果不存在则自动注册
     *
     * @param string $name 服务名称
     * @param array $parameters 参数
     * @return mixed
     * @throws \Exception
     */
    public function get($name, array $parameters = null)
    {
        if (isset(static::$services[$name])) {
            /** @var Service $service 服务实例 */
            $service = static::$services[$name];
        } elseif (class_exists($name)) {
            // 通过类名自动注册并获取服务实例
            $service = $this->set($name, $name);
        } else {
            throw new \Exception("Service '$name' wasn't found in the dependency injection container");
        }

        // 解析服务, 返回实例
        // 如果一个服务注册时使用 shared, 会返回一个 shared 实例, 逻辑在解析方法中体现
        $instance = $service->resolve($parameters);

        // 当前服务实现了 ContainerAwareInterface 接口时，自动为其设置容器
        if ($instance instanceof ContainerAwareInterface) {
            $instance->setDi($this);
        }

        return $instance;
    }

    /**
     * 当一个服务未被注册为单例服务，但是又想获取 shared 实例时
     *
     * @param string $name 服务名称
     * @param array $parameters 参数
     * @return mixed
     */
    public function getShared($name, array $parameters = null)
    {
        // 检查是否已解析
        if (isset(static::$sharedInstances[$name])) {
            return static::$sharedInstances[$name];
        }

        // 解析服务实例
        $service = $this->get($name, $parameters);

        // 保存到 shared 实例列表
        static::$sharedInstances[$name] = $service;

        return $service;
    }

    /**
     * 查询容器中是否存在某个服务
     *
     * @param string $name 服务名称
     * @return bool
     */
    public function has($name)
    {
        return isset(static::$services[$name]);
    }

    /**
     * 从服务容器中删除一个服务
     *
     * @param string $name 服务名称
     * @return void
     */
    public function remove($name)
    {
        unset(static::$services[$name]);
        unset(static::$sharedInstances[$name]);
    }

    /**
     * 获取容器中的所有服务
     *
     * @return array
     */
    public function getServices()
    {
        return static::$services;
    }

    // 实现 \ArrayAccess 方法

    public function offsetExists($name)
    {
        return $this->has($name);
    }

    public function offsetGet($name)
    {
        return $this->getShared($name);
    }

    public function offsetSet($name, $definition)
    {
        return $this->set($name, $definition, true);
    }

    public function offsetUnset($name)
    {
        return false;
    }
}
