<?php

namespace FL\QBJSParser\Tests\Util\Doctrine\Mock\Embeddable;

class MockEmbeddableInsideEmbeddable
{
    private $code;

    private $currency;

    public function getCode()
    {
        return $this->code;
    }

    public function getCurrency()
    {
        return $this->currency;
    }
}
