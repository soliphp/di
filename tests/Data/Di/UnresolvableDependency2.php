<?php

namespace Soli\Tests\Data\Di;

class UnresolvableDependency2
{
    public function __construct($default, D2 $d = null)
    {
    }
}
