<?php

namespace Soli;

/**
 * @property array config
 */
abstract class ServiceProvider extends Component
{
    /**
     * Identifier of the entry to look for.
     *
     * @var string
     */
    protected $id = null;

    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = false;

    /**
     * Register the service provider.
     *
     * @return void
     */
    abstract public function register();

    /**
     * 绑定服务到容器
     */
    public function bind(): void
    {
        if (is_null($this->id)) {
            throw new \InvalidArgumentException('The "id" property must be set.');
        }

        $service = $this;

        $this->container->set(
            $this->id,
            $this->defer ? function () use ($service) {
                return $service->register();
            } : $service->register()
        );
    }
}
