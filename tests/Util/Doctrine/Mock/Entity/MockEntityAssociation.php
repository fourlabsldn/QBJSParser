<?php

namespace FL\QBJSParser\Tests\Util\Doctrine\Mock\Entity;

class MockEntityAssociation
{
    private $id;
    private $description;
    private $embeddable;

    public function getId()
    {
        return $this->id;
    }

    public function getDescription()
    {
        return $this->description;
    }

    public function getEmbeddable()
    {
        return $this->embeddable;
    }
}
