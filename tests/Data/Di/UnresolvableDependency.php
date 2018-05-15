<?php

namespace Soli\Tests\Data\Di;

class UnresolvableDependency
{
    public function __construct($default, D $d)
    {
    }
}
