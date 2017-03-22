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
     * @param \Soli\Di\Container $di
     */
    public function setDi(Container $di);

    /**
     * 获取依赖注入容器
     *
     * @return \Soli\Di\Container
     */
    public function getDi();
}
