<?php

namespace FL\QBJSParser\Tests\Util\Doctrine\Mock\Entity;

class MockEntity
{
    private $id;

    private $price;

    private $name;

    private $date;

    private $privateProperty;

    private $associationEntity;

    private $embeddable;

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

    private function getPrivateProperty()
    {
        return $this->privateProperty;
    }

    public function getAssociationEntity()
    {
        return $this->associationEntity;
    }

    public function getEmbeddable()
    {
        return $this->embeddable;
    }
}
