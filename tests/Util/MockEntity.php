<?php

namespace FL\QBJSParser\Tests\Util;

class MockEntity
{
    private $id;
    private $price;
    private $name;
    private $date;
    public function getId()
    {
        return $this->id;
    }
    public function getPrice()
    {
        return $this->price;
    }
    public function getName()
    {
        return $this->name;
    }
    public function getDate()
    {
        return $this->date;
    }
}