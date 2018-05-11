<?php

namespace Soli\Tests\Data\Di;

class UnresolvableDependency
{
    public function __construct($default, D $d)
    {
    }
}

class D
{
    // 依赖的构造器没有默认值
    public function __construct($notDefaultValue)
    {
    }
}
