Soli Dependency Injection Container
------------------

当前项目参考 [Phalcon 框架的事件管理器]实现。

依赖注入容器的目的为了降低代码的耦合度，提高应用的可维护性。

把组件之间的依赖，转换为对容器的依赖，通过容器进行服务管理(创建、配置和定位)。

## 安装

使用 `composer` 安装到你的项目：

    composer require soliphp/di

## 使用

容器中常用的四个函数，注册服务时的 `set/setShared` 方法和获取服务时的 `get/getShared` 方法。

服务的注册阶段，仅仅是存储服务定义的格式，并不会调用服务的定义，解析出服务的实例；
而在获取服务（即使用服务）时，对服务的定义进行解析，拿到服务实例。
这样便实现了对服务的延迟加载，避免实例化请求中未用到的服务。

### 注册服务

`服务提供者的格式`，只要是 [call_user_func_array] 允许的格式即可，
也可以是实例化类。

#### 使用匿名函数注册服务：

    use Soli\Di\Container;

    $di = new Container();

    // 注册服务，存储服务的定义
    $di->set('some_service', function () {
        new SomeComponent;
    });

    // 注册服务，存储服务的定义
    $di->set('some_service', function () use ($di) {
        var_dump($di->getServices());
    });

将在获取服务时，返回匿名函数的执行结果。

#### 使用类名注册服务

    $di->set('some_service', '\SomeNamespace\SomeComponent');

将在获取服务时，返回对应类名的实例化对象。

#### 使用类函数注册服务

    $di->set('some_service', [new SomeComponent, 'provider']);

将在获取服务时，返回类函数的执行结果。

__更多服务注册的方式，您可以参考 [call_user_func_array] 允许的格式，整理自己的服务提供方式。__


#### 获取服务

    // 获取服务，解析服务定义，并返回服务实例
    $service = $di->get('some_service');

### 共享（单例）服务

共享服务意味着让服务以单例模式运行，之后的每次请求都会从容器取到同一个实例。

#### 注册共享服务

当我们使用 `$di->set()` 方法时，可以传入第三个参数为 true，将服务注册为共享服务：

    $di->set('some_service', <Some definition>, true);

别名为 setShared，以上代码等同于：

    $di->setShared('some_service', <Some definition>);

#### 获取共享服务

当一个服务注册为非共享服务时，我们依然可以通过 getShared 方法获取共享实例：

    $service = $di->getShared('some_service');

对于类名无论是否已注册为服务，我们都可以直接通过容器获取到它的共享实例：

    $service = $di->getShared('\SomeNamespace\SomeComponent');

这对于我们日常开发中经常用到的单例模式，将格外的方便。

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


[Phalcon 框架的事件管理器]: https://docs.phalconphp.com/zh/latest/reference/events.html
[call_user_func_array]: http://cn2.php.net/call_user_func_array
[API 参考]: http://soli-api.aboutc.net/Soli/Di/Container.html
[examples]: examples
[Laravel 框架的服务提供者]: https://laravel.com/docs/5.4/providers
