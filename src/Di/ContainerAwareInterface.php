<?php
/**
 * @author ueaner <ueaner@gmail.com>
 */
namespace Soli\Di;

/**
 * 依赖注入容器感知接口
 */
interface ContainerAwareInterface
{
    /**
     * 设置依赖注入容器
     *
     * @param \Soli\Di\ContainerInterface $container 容器对象实例
     */
    public function setContainer(ContainerInterface $container);

    /**
     * 获取依赖注入容器
     *
     * @return \Soli\Di\ContainerInterface
     */
    public function getContainer();
}
