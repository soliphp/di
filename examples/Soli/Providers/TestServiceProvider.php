<?php

namespace Soli\Providers;

use Soli\ServiceProvider;

class TestServiceProvider extends ServiceProvider
{
    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = true;

    /**
     * Register the service provider.
     *
     * @return string
     */
    public function register()
    {
        return 'This is TestServiceProvider.';
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return ['test', 'test.service', 'test_service'];
    }
}
