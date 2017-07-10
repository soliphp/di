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
     * @return \Soli\Di\ServiceInterface
     */
    public function set($id, $definition);

    /**
     * 注册单例服务
     *
     * @param string $id 服务标识
     * @param mixed $definition 服务定义
     * @return \Soli\Di\ServiceInterface
     */
    public function setShared($id, $definition);

    /**
     * 从容器中获取一个服务
     *
     * 当传入未注册为服务标识的类名时，自动将类名注册为服务，并返回类实例
     *
     * @param string $id 服务标识|类名
     * @return mixed
     */
    public function get($id);

    /**
     * 获取单例服务
     *
     * 当一个服务未被注册为单例服务，使用此方法也可以获取单例服务
     *
     * @param string $id 服务标识
     * @return mixed
     */
    public function getShared($id);

    /**
     * 查询容器中是否存在某个服务
     *
     * @param string $id 服务标识
     * @return bool
     */
    public function has($id);

    /**
     * 从服务容器中删除一个服务
     *
     * @param string $id 服务标识
     * @return void
     */
    public function remove($id);

    /**
     * 获取容器中的某个 Service 对象实例
     *
     * @param string $id 服务标识
     * @return \Soli\Di\ServiceInterface
     */
    public function getService($id);

    /**
     * 获取容器中的所有服务
     *
     * @return \Soli\Di\ServiceInterface[]
     */
    public function getServices();
}
