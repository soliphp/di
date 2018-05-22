<?php

define('APP_PATH', __DIR__);

require APP_PATH . '/bootstrap.php';

$app = new \Soli\ExampleApp();

// 使用访问对象属性的方式，访问容器服务，同 $container->get() 方法
$service = $app->testService;
var_dump($service);

// 使用访问对象属性的方式，访问容器服务，同 $container->get() 方法
$service = $app->container->testService;
var_dump($service);

// 数组访问方式获取 testService 服务，同 $container->get() 方法
$service = $app->container['testService'];
var_dump($service);

// 使用 get 方法获取 testService 服务，
// 由 TestServiceProvider 对象的 defer 属性决定是否延迟执行 register() 方法
$service = $app->container->get('testService');
var_dump($service);

//$service = $app->container->get('redis');
//var_dump($service);
