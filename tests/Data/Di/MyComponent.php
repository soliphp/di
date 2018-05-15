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
