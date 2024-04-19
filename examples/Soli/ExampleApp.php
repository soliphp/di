<?php

namespace Soli;

use Soli\Providers\ConfigServiceProvider;

class ExampleApp extends Component
{
    public function __construct()
    {
        $this->registerBaseServiceProviders();

        $this->registerConfiguredProviders();
        // registerCoreContainerAliases();
    }

    public function registerBaseServiceProviders(): void
    {
        (new ConfigServiceProvider())->bind();
    }

    public function registerConfiguredProviders(): void
    {
        $providers = $this->config['providers'];
        foreach ($providers as $provide) {
            (new $provide())->bind();
        }
    }
}
