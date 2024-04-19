<?php

namespace Soli\Tests;

use Soli\Di\Container;

class TestCase extends \PHPUnit\Framework\TestCase
{
    /** @var \Soli\Di\Container */
    protected static $container;

    public static function setUpBeforeClass(): void
    {
        static::$container = Container::instance() ?: new Container();
    }

    public static function tearDownAfterClass(): void
    {
        static::$container = null;
    }
}
