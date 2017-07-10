<?php
/**
 * @author: ueaner <ueaner@gmail.com>
 */
namespace Soli\Di;

/**
 * ServiceInterface.
 */
interface ServiceInterface
{
    /**
     * 解析服务
     *
     * @param array $parameters 参数
     * @param \Soli\Di\ContainerInterface $container 容器对象实例
     * @return mixed
     */
    public function resolve(array $parameters = null, ContainerInterface $container = null);
}
