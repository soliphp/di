<?php

namespace Soli\Tests\Data\Di;

class D
{
    // 依赖的构造器没有默认值
    public function __construct(string $notDefaultValue)
    {
    }
}
