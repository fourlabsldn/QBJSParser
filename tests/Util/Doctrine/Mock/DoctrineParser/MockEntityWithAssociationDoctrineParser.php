<?php

namespace FL\QBJSParser\Tests\Util\Doctrine\Mock\DoctrineParser;

use FL\QBJSParser\Parser\Doctrine\DoctrineParser;
use FL\QBJSParser\Tests\Util\Doctrine\Mock\Entity\MockEntity;
use FL\QBJSParser\Tests\Util\Doctrine\Mock\Entity\MockEntityAssociation;

class MockEntityWithAssociationDoctrineParser extends DoctrineParser
{
    public function __construct()
    {
        parent::__construct(MockEntity::class,
            [
            'id' => 'id',
            'price' => 'price',
            'name' => 'name',
            'date' => 'date',
            'associationEntity.id' => 'associationEntity.id',
            ],
            [
                'associationEntity' => MockEntityAssociation::class,
            ]
        );
    }
}
