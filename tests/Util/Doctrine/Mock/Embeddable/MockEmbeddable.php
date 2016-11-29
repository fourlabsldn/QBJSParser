<?php

namespace FL\QBJSParser\Tests\Util\Doctrine\Mock\Embeddable;

class MockEmbeddable
{
    private $startDate;
    private $endDate;
    private $embeddableInsideEmbeddable;

    public function getStartDate()
    {
        return $this->startDate;
    }

    public function getEndDate()
    {
        return $this->endDate;
    }

    public function getEmbeddableInsideEmbeddable()
    {
        return $this->embeddableInsideEmbeddable;
    }
}
