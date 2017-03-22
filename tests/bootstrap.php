<?php

// 时区
date_default_timezone_set('Asia/Shanghai');

// 显示错误
error_reporting(-1);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

// 即使捕捉到异常，xdebug.show_exception_trace = On 时仍会强制执行异常跟踪
// @see https://xdebug.org/docs/stack_trace#show_exception_trace
ini_set('xdebug.show_exception_trace', 0);

// 不使用 session cookies
ini_set('session.use_cookies', 0);

// Enable Composer autoloader
/** @var \Composer\Autoload\ClassLoader $autoloader */
$autoloader = require dirname(__DIR__) . '/vendor/autoload.php';

// Register test classes
$autoloader->addPsr4("Soli\\Tests\\", __DIR__);
