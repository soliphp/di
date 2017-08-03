<?php

namespace Soli\Tests\Data\Di;

use Soli\Di\ContainerAwareInterface;
use Soli\Di\ContainerAwareTrait;

class MyComponent implements ContainerAwareInterface
{
    use ContainerAwareTrait;

    protected $id;

    public function __construct($id = 0)
    {
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
