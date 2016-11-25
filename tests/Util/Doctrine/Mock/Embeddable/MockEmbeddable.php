<?php

namespace FL\QBJSParser\Tests\Util\Doctrine\Mock\Embeddable;

class MockEmbeddable
{
    private $startDate;
    private $endDate;

    public function getStartDate()
    {
        return $this->startDate;
    }

    public function getEndDate()
    {
        return $this->endDate;
    }
}
