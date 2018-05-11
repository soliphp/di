<?php

namespace Soli\Tests\Data\Di;

use Soli\Di\ContainerAwareInterface;
use Soli\Di\ContainerAwareTrait;

class MyComponent implements ContainerAwareInterface
{
    use ContainerAwareTrait;

    protected $id;

    public $a;

    public function __construct(A $a, $id = 0)
    {
        $this->a = $a;

        if ($id) {
            $this->id = $id;
        } else {
            $this->id = microtime(true) . ''. mt_rand();
        }
    }

    public function getId()
    {
        return $this->id;
    }
}


class A
{
    public $b;
    public $c;
    public $d;

    public function __construct(B $b, C $c, $d = null)
    {
        $this->b = $b;
        $this->c = $c;
        $this->d = $d;
    }
}

class B
{
    public function __construct()
    {
    }
}

class C
{
    public function __construct()
    {
    }
}
