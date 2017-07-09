<?php

namespace Soli\Providers;

use Soli\ServiceProvider;

class TestServiceProvider extends ServiceProvider
{
    protected $id = 'testService';

    protected $defer = true;

    public function register()
    {
        return 'This is TestServiceProvider.';
    }
}
