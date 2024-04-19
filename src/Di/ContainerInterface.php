<?php
/**
 * @author: ueaner <ueaner@gmail.com>
 */

namespace Soli\Di;

use Psr\Container\ContainerInterface as PsrContainerInterface;

/**
 * ContainerInterface.
 */
interface ContainerInterface extends PsrContainerInterface
{
    /**
     * 注册一个服务到容器
     *
     * @param string $id 服务标识
     * @param mixed $definition 服务定义
     * @param bool $shared
     * @return \Soli\Di\ServiceInterface
     */
    public function set($id, $definition, $shared = true): ServiceInterface;

    /**
     * 从容器中获取一个服务
     *
     * 当传入未注册为服务标识的类名时，自动将类名注册为服务，并返回类实例
     *
     * @param string $id 服务标识|类名
     * @return mixed
     */
    public function get($id): mixed;

    /**
     * 为服务添加别名
     *
     * @param string $alias
     * @param string $abstract
     * @return void
     */
    public function alias($alias, $abstract): void;

    /**
     * 查询容器中是否存在某个服务
     *
     * @param string $id 服务标识
     * @return bool
     */
    public function has($id): bool;

    /**
     * 从服务容器中删除一个服务
     *
     * @param string $id 服务标识
     * @return void
     */
    public function remove($id): void;

    /**
     * 清空容器
     *
     * @return void
     */
    public function clear(): void;
}
