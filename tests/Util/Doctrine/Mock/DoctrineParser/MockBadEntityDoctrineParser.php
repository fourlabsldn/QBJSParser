<?php

namespace FL\QBJSParser\Tests\Util\Doctrine\Mock\DoctrineParser;

use FL\QBJSParser\Parser\Doctrine\DoctrineParser;
use FL\QBJSParser\Tests\Util\Doctrine\Mock\Entity\MockBadEntity;

class MockBadEntityDoctrineParser extends DoctrineParser
{
    public function __construct()
    {
        parent::__construct(MockBadEntity::class, [
            'id' => 'id',
            'price' => 'price',
            'name' => 'name',
            'date' => 'date',
        ], []);
    }
}
