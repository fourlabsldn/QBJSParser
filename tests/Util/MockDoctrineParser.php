<?php

namespace FL\QBJSParser\Tests\Util;

use FL\QBJSParser\Parser\Doctrine\AbstractDoctrineParser;

class MockDoctrineParser extends AbstractDoctrineParser
{
    /**
     * MockDoctrineParser constructor.
     * @param string $className
     */
    public function __construct(string $className)
    {
        /**
         * Will work fine when used with
         * @see MockEntity
         *
         * Will trigger errors when used with
         * @see MockBadEntity
         * @see MockBadEntity2
         */
        parent::__construct($className,[
            'id' => 'id',
            'price' => 'price',
            'name' => 'name',
            'date' => 'date',
        ]);
    }
}
