<?php

namespace Soli\Tests\Data\Di;

class UnresolvableDependency2
{
    public function __construct($default, D2 $d = null)
    {
    }
}

class D2
{
    // 依赖的构造器没有默认值
    public function __construct($notDefaultValue)
    {
    }
}
