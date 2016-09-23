<?php

namespace FL\QBJSParser\Tests\Parser\Doctrine;

use FL\QBJSParser\Exception\Parser\Doctrine\InvalidClassNameException;
use FL\QBJSParser\Exception\Parser\Doctrine\FieldMappingException;
use FL\QBJSParser\Model\Rule;
use FL\QBJSParser\Model\RuleGroup;
use FL\QBJSParser\Model\RuleGroupInterface;
use FL\QBJSParser\Parser\Doctrine\DoctrineParser;
use FL\QBJSParser\Parser\Doctrine\JoinPartialParser;
use FL\QBJSParser\Tests\Util\MockBadEntity2DoctrineParser;
use FL\QBJSParser\Tests\Util\MockBadEntityDoctrineParser;
use FL\QBJSParser\Tests\Util\MockEntity;
use FL\QBJSParser\Tests\Util\MockEntityDoctrineParser;
use FL\QBJSParser\Tests\Util\MockEntityWithAssociationDoctrineParser;

class JoinPartialParserTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function parseTest()
    {
        $queryBuilderFieldPrefixesToAssociationClasses = [
            'labels' => 'AppBundle\Entity\Label',
            'specification' => 'AppBundle\Entity\Specification',
            'labels.specification' => 'AppBundle\Entity\Label',
        ];
        $parsed = JoinPartialParser::parse($queryBuilderFieldPrefixesToAssociationClasses);
        $expected = ' JOIN  object.labels   object_labels   JOIN  object.specification   object_specification   JOIN  object_labels.specification   object_labels_specification  ';

        $this->assertEquals($expected, $parsed);
    }
}
