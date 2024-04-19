<?php

namespace Soli\Tests\Data\Di;

class UnresolvableDependency
{
    public function __construct(string $default, D $d)
    {
    }
}
