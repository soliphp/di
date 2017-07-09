<?php

namespace Soli\Providers;

use Soli\ServiceProvider;

class ConfigServiceProvider extends ServiceProvider
{
    protected $id = 'config';

    protected $defer = true;

    public function register()
    {
        return require APP_PATH . '/config.php';
    }
}
