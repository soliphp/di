<?php

define('APP_PATH', __DIR__);

require APP_PATH . '/bootstrap.php';

$app = new \Soli\ExampleApp();

$app->registerBaseServiceProviders();

$app->registerConfiguredProviders();

$service = $app->test_service;
var_dump($service);

// 对于不能用作属性名的服务名称，只能使用 get/getShared 方法获取
$service = $app->di->get('test.service');
var_dump($service);

//$service = $app->di->get('redis.connection');
//var_dump($service);
