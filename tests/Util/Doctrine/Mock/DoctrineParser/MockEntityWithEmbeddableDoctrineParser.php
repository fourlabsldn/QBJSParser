<?php

namespace FL\QBJSParser\Tests\Util\Doctrine\Mock\DoctrineParser;

use FL\QBJSParser\Parser\Doctrine\DoctrineParser;
use FL\QBJSParser\Tests\Util\Doctrine\Mock\Entity\MockEntity;
use FL\QBJSParser\Tests\Util\Doctrine\Mock\Entity\MockEntityAssociation;

class MockEntityWithEmbeddableDoctrineParser extends DoctrineParser
{
    public function __construct()
    {
        parent::__construct(MockEntity::class,
            [
            'id' => 'id',
            'price' => 'price',
            'name' => 'name',
            'date' => 'date',
            ],
            [
                'associationEntity' => MockEntityAssociation::class,
            ],
            [
                'embeddable.startDate' => 'embeddable.startDate',
                'associationEntity.embeddable.startDate' => 'associationEntity.embeddable.startDate'
            ],
            [
                'embeddable' => MockEntityAssociation::class,
                'associationEntity' => MockEntityAssociation::class,
                'associationEntity.embeddable' => MockEntityAssociation::class,
            ]
        );
    }
}
