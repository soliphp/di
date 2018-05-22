Soli Dependency Injection Container
------------------

当前项目参考了 Phalcon 框架的[依赖注入与服务定位器] 和 Laravel 框架的[服务容器] 实现。

服务容器的目的为了降低代码的耦合度，提高应用的可维护性；是用于管理类依赖和执行依赖注入的工具。

[![Build Status](https://travis-ci.org/soliphp/di.svg?branch=master)](https://travis-ci.org/soliphp/di)
[![Coverage Status](https://coveralls.io/repos/github/soliphp/di/badge.svg?branch=master)](https://coveralls.io/github/soliphp/di?branch=master)
[![License](https://poser.pugx.org/soliphp/di/license)](https://packagist.org/packages/soliphp/di)


## Table of Contents

* [安装](#安装)
* [先看一个简单的例子](#先看一个简单的例子)
* [注册服务](#注册服务)
   * [类名](#类名)
   * [类实例](#类实例)
   * [闭包/匿名函数](#闭包匿名函数)
      * [使用 $this 访问容器中的其他服务](#使用-this-访问容器中的其他服务)
   * [注册接口到实现](#注册接口到实现)
   * [共享（单例）服务](#共享单例服务)
* [获取服务](#获取服务)
   * [get()](#get)
   * [对象属性](#对象属性)
   * [数组下标](#数组下标)
   * [类名](#类名-1)
   * [$this](#this)
* [静态方式访问容器](#静态方式访问容器)
* [容器感知](#容器感知)
* [别名](#别名)
   * [为已注册服务定义别名](#为已注册服务定义别名)
   * [为类名定义别名](#为类名定义别名)
* [示例](#示例)
* [API 参考](#api-参考)
* [测试](#测试)
* [License](#license)

## 安装

使用 `composer` 进行安装：

    composer require soliphp/di

## 先看一个简单的例子

    use Psr\Log\LoggerInterface;

    class ExceptionHandler
    {
        /**
         * 日志记录器
         *
         * @var \Psr\Log\LoggerInterface
         */
        protected $logger;

        public function __construct(LoggerInterface $logger)
        {
            $this->logger = $logger;
        }

        /**
         * 异常报告
         */
        public function report(\Throwable $e)
        {
            $this->logger->error($e->getMessage());
        }
    }


异常处理类 `ExceptionHandler` 需要 `logger` 记录（报告）异常，
因此，我们需要`注入`实现了 `Psr\Log\LoggerInterface` 接口的类。
而 `logger` 的实现是要发短信、发邮件或进日志系统，这个 `ExceptionHandler`
是不关心的，所以我们可以轻易地将 `logger` 切换为另一个实现。
在为应用编写测试时，也可以轻松的模拟 `Psr\Log\LoggerInterface` 的实现。

## 注册服务

服务的注册阶段，仅仅是存储服务定义的格式，并不会调用服务的定义；
而在获取服务（即使用服务）时，对服务定义进行调用，得到服务定义的执行结果。
这样便实现了对`服务的延迟加载`，避免实例化请求中未用到的服务。

以下 `$container` 变量均为 `Soli\Di\Container` 实例。

### 类名

    use Soli\Di\Container;

    $container = new Container();

    $container->set('someService', \SomeNamespace\SomeService::class);

### 类实例

    $container->set('someService', new \SomeNamespace\SomeService());

### 闭包/匿名函数

    // 注册服务，存储服务的定义
    $container->set('someService', function () {
        return new SomeService();
    });

#### 使用 $this 访问容器中的其他服务

当使用匿名函数注册服务时，函数体内可以使用 `$this` 表示当前的 `$container` 容器类实例，
直接访问容器中的其他服务，便于服务之间进行交互。

    // 注册服务，存储服务的定义
    $container->set('someService', function () {
        // 此处的 $tihs 即是当前的 $container 容器类实例
        // 通过 $this 直接访问容器中的其他服务
        var_dump($this->otherService);
    });

### 注册接口到实现

`注册接口到实现类`：

    $container->set('Database\AdapterInterface', 'Database\Adapter\Mysql');

如果一个类需要处理后才会得到一个适用的实例，那么这里的实现就是一个匿名函数，
可以`注册接口到匿名函数`，注意匿名函数的返回实例即可：

    use Monolog\Logger;
    use Monolog\Handler\StreamHandler;

    $container->set('Psr\Log\LoggerInterface', function () {
        // create a log channel
        $log = new Logger('name');
        $log->pushHandler(new StreamHandler('/path/to/your.log', Logger::WARNING));

        return $log;
    });

### 共享（单例）服务

共享服务意味着让服务以单例模式运行，之后的每次请求都会从容器取到同一个实例。
否则每次都会执行服务解析，返回新的服务实例。

`默认使用 set() 注册的服务都为共享服务`。

如果需要`每次都使用新的实例`，则需要将第三个参数 `$shared 设置为 false`。

    $container->set('newInstance', 'SomeNamespace\SomeService', false);

## 获取服务

获取服务时，调用服务定义，返回服务实例。

获取服务有多种方法：

### get()

使用 `get()` 方法获取服务：

    $service = $container->get('someService');

### 对象属性

使用访问`对象属性`的方式，获取服务：

    $service = $container->someService;

### 数组下标

使用访问`数组下标`的方式，获取服务：

    $service = $container['someService'];

### 类名

对于`类名`无论是否已注册为服务，都可以直接通过容器获取到它的单例：

    $service = $container->get('SomeNamespace\UnregisteredClass');

这对于我们日常开发中经常用到的单例模式，将格外的方便。

### $this

匿名函数注册服务时[使用 $this 访问容器中的其他服务](#使用-this-访问容器中的其他服务)。

## 静态方式访问容器

    $container = Container::instance();

    $container->get('someService');

这里需要注意一下，在调用 `Container::instance()` 之前，一定已经执行过 `new Container()` 实例化操作。

## 容器感知

如果某个服务实现了 `Soli\Di\ContainerAwareInterface` 容器感知接口，
在获取服务时，会自动调用 `setContainer()` 为其设置容器实例。

## 别名

别名是用来给服务起不同的名字，以便可以使用不同的名字获取同一个服务。

注意不要定义与服务名称相同的别名。

### 为已注册服务定义别名

首先注册以 `container` 为名称的服务：

    $container->set('container', $container);

为 `container` 服务定义三个别名 `Soli\Di\Container`, `Soli\Di\ContainerInterface`, `Psr\Container\ContainerInterface`：

    $container->alias('Soli\Di\Container', 'container');
    $container->alias('Soli\Di\ContainerInterface', 'container');
    $container->alias('Psr\Container\ContainerInterface', 'container');

如果容器中其他服务的构造函数使用了以上三个别名的类型提示，则会为其自动注入对应服务实例。

    use Psr\Container\ContainerInterface;

    class Component
    {
        protected $container;

        public function __construct(ContainerInterface $container)
        {
            $this->container = $container;
        }
    }

    // 将自动注入 $container 参数
    $component = $container->get(Component::class);

### 为类名定义别名

由于类名可直接通过容器获取其实例，所以类名是无需定义的服务名称。

那么我们便可以直接为类名定义别名：

    $container->alias('Soli\Di\ContainerInterface', 'Soli\Di\Container');
    $container->alias('Psr\Container\ContainerInterface', 'Soli\Di\Container');

## 示例

在 [examples] 文件夹下提供了一个类似 [Laravel 框架的服务提供者]的例子，感兴趣的同学可以前去翻看。

运行方法：

    $ cd /path/to/soliphp/di/
    $ composer install
    $ php examples/index.php

## API 参考

[API 参考]

## 测试

    $ cd /path/to/soliphp/di/
    $ composer install
    $ phpunit

## License

MIT Public License

[依赖注入与服务定位器]: https://docs.phalconphp.com/en/latest/di
[服务容器]: https://laravel.com/docs/master/container
[API 参考]: http://soli-api.aboutc.net/Soli/Di.html
[examples]: examples
[Laravel 框架的服务提供者]: https://laravel.com/docs/5.4/providers
