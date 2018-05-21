<?php
/**
 * @author ueaner <ueaner@gmail.com>
 */
namespace Soli\Di;

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
     * 存储容器对象实例
     *
     * @var \Soli\Di\ContainerInterface
     */
    public static $instance;

    /**
     * 存储所有注册的服务
     *
     * @var \Soli\Di\ServiceInterface[]
     */
    protected $services = [];

    /**
     * 存储共享服务实例
     *
     * @var array
     */
    protected $sharedInstances = [];

    /**
     * 别名列表
     *
     * @var array
     */
    public $aliases = [];

    /**
     * 初始化容器默认实例
     */
    public function __construct()
    {
        if (static::$instance === null) {
            static::$instance = $this;
        }
        return static::$instance;
    }

    /**
     * 获取容器对象实例
     *
     * @return \Soli\Di\ContainerInterface
     */
    public static function instance()
    {
        return static::$instance;
    }

    /**
     * 注册一个服务到容器
     *
     * @param string $id 服务标识
     * @param mixed $definition 服务定义
     * @param bool $shared 是否为共享实例，默认为共享实例
     * @return \Soli\Di\ServiceInterface
     */
    public function set($id, $definition, $shared = true)
    {
        unset($this->sharedInstances[$id], $this->aliases[$id]);

        $service = new Service($id, $definition, $shared);
        $this->services[$id] = $service;
        return $service;
    }

    /**
     * 从容器中获取一个服务
     *
     * 当传入未注册为服务标识的类名时，自动将类名注册为服务，并返回类实例
     *
     * @param string $id 服务标识|类名|别名
     * @param array $parameters 参数
     * @return mixed
     */
    public function get($id, array $parameters = [])
    {
        $id = $this->getAliasId($id);

        // 如果是共享实例已解析，则返回
        if (isset($this->sharedInstances[$id])) {
            return $this->sharedInstances[$id];
        }

        if (isset($this->services[$id])) {
            /** @var \Soli\Di\ServiceInterface $service 服务实例 */
            $service = $this->services[$id];
        } elseif (class_exists($id)) {
            // 自动将类名注册为服务
            $service = $this->set($id, $id);
        } else {
            throw new \InvalidArgumentException("Service '$id' wasn't found in the dependency injection container");
        }

        // 解析服务, 返回服务定义的执行结果
        $instance = $service->resolve($parameters, $this);

        // 当前服务实现了 ContainerAwareInterface 接口时，自动为其设置容器
        if ($instance instanceof ContainerAwareInterface) {
            $instance->setContainer($this);
        }

        // 保存到共享实例列表
        if ($service->isShared()) {
            $this->sharedInstances[$id] = $instance;
        }

        return $instance;
    }

    /**
     * 为某个服务定义别名，主要用于类型提示（接口）的自动注入
     *
     * @param string $alias 别名（接口名）
     * @param string $id 服务标识|类名
     * @return void
     */
    public function alias($alias, $id)
    {
        $this->aliases[$alias] = $id;
    }

    /**
     * 获取某个别名对应的服务标识
     *
     * @param string $alias 别名
     * @return string
     */
    public function getAliasId($alias)
    {
        if (!isset($this->aliases[$alias])) {
            return $alias;
        }

        if ($this->aliases[$alias] === $alias) {
            throw new \LogicException("[{$alias}] is aliased to itself.");
        }

        return $this->getAliasId($this->aliases[$alias]);
    }

    /**
     * 查询容器中是否存在某个服务
     *
     * @param string $id 服务标识
     * @return bool
     */
    public function has($id)
    {
        return isset($this->services[$id]) || isset($this->aliases[$id]);
    }

    /**
     * 从服务容器中删除一个服务
     *
     * @param string $id 服务标识
     * @return void
     */
    public function remove($id)
    {
        unset($this->services[$id]);
        unset($this->sharedInstances[$id]);
        unset($this->aliases[$id]);
    }

    /**
     * 清空容器
     *
     * @return void
     */
    public function clear()
    {
        $this->services = [];
        $this->sharedInstances = [];
        $this->aliases = [];
    }

    // 实现 \ArrayAccess 方法

    public function offsetExists($id)
    {
        return $this->has($id);
    }

    public function offsetGet($id)
    {
        return $this->get($id);
    }

    public function offsetSet($id, $definition)
    {
        $this->set($id, $definition);
    }

    public function offsetUnset($id)
    {
        $this->remove($id);
    }

    /**
     * 允许将服务标识作为属性名访问
     *
     *<pre>
     * $container->someService;
     *</pre>
     *
     * @param string $id 服务标识
     * @return mixed
     */
    public function __get($id)
    {
        return $this->get($id);
    }
}
