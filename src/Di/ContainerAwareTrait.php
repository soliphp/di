<?php
/**
 * @author ueaner <ueaner@gmail.com>
 */
namespace Soli\Di;

/**
 * ContainerAwareTrait
 */
trait ContainerAwareTrait
{
    /**
     * @var \Soli\Di\ContainerInterface
     */
    protected $container;

    /**
     * 设置依赖注入容器
     *
     * @param \Soli\Di\ContainerInterface $container 容器对象实例
     */
    public function setContainer(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * 获取依赖注入容器
     *
     * @return \Soli\Di\ContainerInterface
     */
    public function getContainer()
    {
        return $this->container;
    }
}
