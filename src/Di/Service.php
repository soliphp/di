<?php
/**
 * @author ueaner <ueaner@gmail.com>
 */
namespace Soli\Di;

use Closure;
use ReflectionClass;

/**
 * 服务原型
 *
 * 容器中对单个服务单元的定义
 */
class Service implements ServiceInterface
{
    /**
     * 服务标识
     *
     * @var string $id
     */
    protected $id;

    /**
     * 服务定义, 类名|对象实例或Closure
     *
     * @var object|string|array
     */
    protected $definition;

    /**
     * 是否为共享服务
     *
     * @var bool
     */
    protected $shared = false;

    /**
     * 存储共享服务实例（即服务定义的执行结果）
     *
     * @var mixed
     */
    protected $sharedInstance;

    /**
     * Service constructor.
     *
     * @param string $id 服务标识
     * @param object|string $definition
     * @param bool $shared
     */
    public function __construct($id, $definition, $shared = false)
    {
        $this->id = $id;
        $this->definition = $definition;
        $this->shared = (bool)$shared;
    }

    /**
     * 检查服务是否为共享的
     *
     * @return bool
     */
    public function isShared()
    {
        return $this->shared;
    }

    /**
     * 解析服务
     *
     * @param array $parameters 参数
     * @param \Soli\Di\ContainerInterface $di 容器对象实例
     * @return mixed
     * @throws \DomainException
     */
    public function resolve(array $parameters = null, ContainerInterface $di = null)
    {
        // 为 shared 服务且解析过则直接返回实例
        if ($this->shared && $this->sharedInstance !== null) {
            return $this->sharedInstance;
        }

        // 创建实例
        $instance = null;
        $definition = $this->definition;
        $type = gettype($definition);

        switch ($type) {
            case 'object':
                if ($definition instanceof Closure) {
                    // 绑定匿名函数到当前的依赖注入容器对象实例上
                    // 以便匿名函数内可以可以通过
                    if (is_object($di)) {
                        $definition = Closure::bind($definition, $di);
                    }

                    // Closure
                    $instance = $this->createInstanceFromClosure($definition, $parameters);
                } else {
                    // 对象实例
                    $instance = $definition;
                }
                break;
            case 'string':
                // 已存在的类名
                $instance = $this->createInstanceFromClassName($definition, $parameters);
                break;
            default:
                throw new \DomainException("Service '{$this->id}' cannot be resolved");
        }

        // 如果是 shared, 保存实例
        if ($this->shared) {
            $this->sharedInstance = $instance;
        }

        return $instance;
    }

    /**
     * @param Closure $closure
     * @param array   $parameters
     * @return mixed
     */
    protected function createInstanceFromClosure(Closure $closure, array $parameters = null)
    {
        // Closure
        if (is_array($parameters) && count($parameters)) {
            $instance = call_user_func_array($closure, $parameters);
        } else {
            $instance = call_user_func($closure);
        }

        return $instance;
    }

    /**
     * @param string $className
     * @param array  $parameters
     * @return object
     * @throws \DomainException
     */
    protected function createInstanceFromClassName($className, array $parameters = null)
    {
        if (!class_exists($className)) {
            throw new \DomainException("Service '{$this->id}' cannot be resolved");
        }

        $reflector = new ReflectionClass($className);

        if (is_array($parameters) && count($parameters)) {
            $instance = $reflector->newInstanceArgs($parameters);
        } else {
            $instance = $reflector->newInstance();
        }

        return $instance;
    }
}
