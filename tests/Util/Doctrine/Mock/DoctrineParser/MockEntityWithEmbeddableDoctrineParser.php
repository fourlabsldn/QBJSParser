<?php

namespace FL\QBJSParser\Tests\Util\Doctrine\Mock\DoctrineParser;

use FL\QBJSParser\Parser\Doctrine\DoctrineParser;
use FL\QBJSParser\Tests\Util\Doctrine\Mock\Entity\MockEntity;
use FL\QBJSParser\Tests\Util\Doctrine\Mock\Entity\MockEntityAssociation;
use FL\QBJSParser\Tests\Util\Doctrine\Mock\Embeddable\MockEmbeddable;

class MockEntityWithEmbeddableDoctrineParser extends DoctrineParser
{
    public function __construct()
    {
        parent::__construct(
            MockEntity::class,
            [
                'id' => 'id',
                'price' => 'price',
                'name' => 'name',
                'date' => 'date',
                'associationEntity.id' => 'associationEntity.id'
            ],
            [
                'associationEntity' => MockEntityAssociation::class,
            ],
            [
                'embeddable.startDate' => 'embeddable.startDate',
                'embeddable.endDate' => 'embeddable.endDate',
                'associationEntity.embeddable.startDate' => 'associationEntity.embeddable.startDate',
                'associationEntity.embeddable.endDate' => 'associationEntity.embeddable.endDate'
            ],
            [
                'embeddable' => MockEmbeddable::class,
                'associationEntity.embeddable' => MockEmbeddable::class,
            ],
            [
                'associationEntity' => MockEntityAssociation::class,
            ]
        );
    }
}
