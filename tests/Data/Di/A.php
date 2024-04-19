<?php

namespace Soli\Tests\Data\Di;

class A
{
    public $b;
    public $c;
    public $d;

    public function __construct(B $b, C $c, D $d = null)
    {
        $this->b = $b;
        $this->c = $c;
        $this->d = $d;
    }
}
