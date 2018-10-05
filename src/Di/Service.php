<?php
/**
 * @author ueaner <ueaner@gmail.com>
 */
namespace Soli\Di;

use Closure;
use ReflectionClass;
use ReflectionFunction;
use ReflectionParameter;

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
     * 服务定义, Closure|对象实例|类名|数组
     *
     * @var \Closure|object|string|array
     */
    protected $definition;

    /**
     * 是否为共享服务
     *
     * @var bool
     */
    protected $shared = false;

    /**
     * 传入的参数
     *
     * @var array
     */
    protected $parameters;

    /**
     * 容器实例
     *
     * @var \Soli\Di\ContainerInterface;
     */
    protected $container;

    /**
     * Service constructor.
     *
     * @param string $id 服务标识
     * @param object|string|array $definition
     * @param bool $shared
     */
    public function __construct($id, $definition, $shared = false)
    {
        $this->id = $id;
        $this->definition = $definition;
        $this->shared = $shared;
    }

    /**
     * 服务是否为共享的
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
     * @param \Soli\Di\ContainerInterface $container 容器对象实例
     * @return mixed
     * @throws \Exception
     */
    public function resolve(array $parameters = [], ContainerInterface $container = null)
    {
        $this->parameters = $parameters;
        $this->container = $container;

        // 创建实例
        $instance = null;
        $definition = $this->definition;
        $type = gettype($definition);

        switch ($type) {
            case 'object':
                if ($definition instanceof Closure) {
                    $instance = $this->buildClosure();
                } else {
                    // 对象实例
                    $instance = $definition;
                }
                break;
            case 'string':
                // 已存在的类名
                $instance = $this->buildClass();
                break;
            default:
                throw new \DomainException("Service '{$this->id}' cannot be resolved");
        }

        return $instance;
    }

    /**
     * @return mixed
     * @throws \ReflectionException
     */
    protected function buildClosure()
    {
        $container = $this->container;
        $closure = $this->definition;

        // 匿名函数内使用 $this 访问容器中的其他服务
        if (is_object($container)) {
            $closure = $closure->bindTo($container);
        }

        $reflector = new ReflectionFunction($closure);

        // ReflectionParameter[]
        $dependencies = $reflector->getParameters();

        $instances = $this->resolveDependencies(
            $dependencies
        );

        return $closure(...$instances);
    }

    /**
     * @return object
     * @throws \Exception
     */
    protected function buildClass()
    {
        $className = $this->definition;

        if (!class_exists($className)) {
            throw new \DomainException("Service '{$this->id}' cannot be resolved");
        }

        $reflector = new ReflectionClass($className);

        if (!$reflector->isInstantiable()) {
            throw new \DomainException("Can not instantiate {$reflector->name}");
        }

        // ReflectionMethod
        $constructor = $reflector->getConstructor();
        if (is_null($constructor)) {
            return $reflector->newInstance();
        }

        // ReflectionParameter[]
        $dependencies = $constructor->getParameters();

        $instances = $this->resolveDependencies(
            $dependencies
        );

        return $reflector->newInstanceArgs($instances);
    }

    /**
     * @param array $dependencies
     * @return array
     * @throws \Exception
     */
    protected function resolveDependencies(array $dependencies)
    {
        $parameters = $this->parameters;

        $results = [];

        foreach ($dependencies as $dependency) {
            // 优先使用传入的参数值
            if (array_key_exists($dependency->name, $parameters)) {
                $results[] = $parameters[$dependency->name];
                continue;
            }

            $results[] = is_null($dependency->getClass())
                ? $this->resolvePrimitive($dependency)
                : $this->resolveClass($dependency);
        }

        return $results;
    }

    /**
     * @param \ReflectionParameter $parameter
     * @return mixed
     * @throws \Exception
     */
    protected function resolvePrimitive(ReflectionParameter $parameter)
    {
        if ($parameter->isDefaultValueAvailable()) {
            return $parameter->getDefaultValue();
        }

        $message = sprintf(
            "Unresolvable dependency resolving [%s] in class %s",
            $parameter,
            $parameter->getDeclaringClass()->getName()
        );

        throw new \DomainException($message);
    }

    /**
     * @param \ReflectionParameter $parameter
     * @return mixed
     * @throws \Exception
     */
    protected function resolveClass(ReflectionParameter $parameter)
    {
        try {
            return $this->container->get($parameter->getClass()->name);
        } catch (\Exception $e) {
            if ($parameter->isOptional()) {
                return $parameter->getDefaultValue();
            }

            throw $e;
        }
    }
}
