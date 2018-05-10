Soli Dependency Injection Container
------------------

当前项目参考 [Phalcon 框架的依赖注入与服务定位器]实现。

依赖注入容器的目的为了降低代码的耦合度，提高应用的可维护性。

把组件之间的依赖，转换为对容器的依赖，通过容器进行服务管理(创建、配置和定位)。

[![Build Status](https://travis-ci.org/soliphp/di.svg?branch=master)](https://travis-ci.org/soliphp/di)
[![Coverage Status](https://coveralls.io/repos/github/soliphp/di/badge.svg?branch=master)](https://coveralls.io/github/soliphp/di?branch=master)
[![License](https://poser.pugx.org/soliphp/di/license)](https://packagist.org/packages/soliphp/di)


## Table of Contents

* [安装](#安装)
* [使用](#使用)
   * [注册服务](#注册服务)
      * [使用匿名函数注册服务](#使用匿名函数注册服务)
      * [使用对象实例注册服务](#使用对象实例注册服务)
      * [使用类名注册服务](#使用类名注册服务)
      * [使用数组注册服务](#使用数组注册服务)
      * [获取服务](#获取服务)
   * [共享（单例）服务](#共享单例服务)
      * [注册共享服务](#注册共享服务)
      * [获取共享服务](#获取共享服务)
      * [静态方式访问容器](#静态方式访问容器)
* [示例](#示例)
* [API 参考](#api-参考)
* [测试](#测试)
* [License](#license)

## 安装

使用 `composer` 安装到你的项目：

    composer require soliphp/di

## 使用

容器中常用的四个方法，注册服务时的 `set/setShared` 方法和获取服务时的 `get/getShared` 方法。

服务的注册阶段，仅仅是存储服务定义的格式，并不会调用服务的定义；
而在获取服务（即使用服务）时，对服务定义进行调用，得到服务定义的执行结果。
这样便实现了对服务的延迟加载，避免实例化请求中未用到的服务。

### 注册服务

`服务提供者的格式`，可以是 `匿名函数、对象实例、类名或数组`。

#### 使用匿名函数注册服务

    use Soli\Di\Container;

    $container = new Container();

    // 注册服务，存储服务的定义
    $container->set('someComponent', function () {
        return new SomeComponent;
    });

*使用 $this 访问容器中的其他服务*

`当使用匿名函数注册服务时，函数体内可以使用 $this 表示当前的 $container 容器对象实例，
直接访问容器中的其他服务，便于服务之间进行交互。`

    // 注册服务，存储服务的定义
    $container->set('someService', function () {
        // 此处的 $tihs 即是当前的 $container 容器对象实例
        var_dump($this->getServices());

        // 通过 $this 直接访问容器中的其他服务
        var_dump($this->someComponent);
    });

将在获取服务时，返回匿名函数的执行结果。

#### 使用对象实例注册服务

    $container->set('someComponent', new \SomeNamespace\SomeComponent());

将在获取服务时，返回对应的对象实例。

#### 使用类名注册服务

    $container->set('someComponent', '\SomeNamespace\SomeComponent');

将在获取服务时，返回对应类名的实例化对象。

#### 使用数组注册服务

注：`注册数组类型数据作为服务，仅作为存储，不做任何解析和转换。`

    $config = [
        'application' => [
            'viewsDir'       => __DIR__ . '/views/',
            'logsDir'        => __DIR__ . '/logs/',
            'cacheDir'       => __DIR__ . '/cache/',
        ],
        // ...
    ];

    $container->set('config', $config);

将在获取服务时，返回注册的数组信息。

#### 获取服务

    // 获取服务，调用服务定义，返回服务定义的执行结果
    $service = $container->get('someService');

### 共享（单例）服务

共享服务意味着让服务以单例模式运行，之后的每次请求都会从容器取到同一个实例。

#### 注册共享服务

与 set 方法对应，我们可以使用 setShared 方法，将服务注册为共享服务：

    $container->setShared('someService', <Some definition>);

#### 获取共享服务

当一个服务注册为非共享服务时，我们依然可以通过 getShared 方法获取共享实例：

    $service = $container->getShared('someService');

对于类名无论是否已注册为服务，我们都可以直接通过容器获取到它的共享实例：

    $service = $container->getShared('\SomeNamespace\SomeComponent');

这对于我们日常开发中经常用到的单例模式，将格外的方便。

#### 静态方式访问容器

    Container::instance()->getShared('someService');

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


[Phalcon 框架的依赖注入与服务定位器]: https://docs.phalconphp.com/en/latest/di
[API 参考]: http://soli-api.aboutc.net/Soli/Di.html
[examples]: examples
[Laravel 框架的服务提供者]: https://laravel.com/docs/5.4/providers
