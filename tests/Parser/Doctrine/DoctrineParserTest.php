<?php

namespace FL\QBJSParser\Tests\Parser\Doctrine;

use FL\QBJSParser\Model\Rule;
use FL\QBJSParser\Model\RuleGroup;
use FL\QBJSParser\Model\RuleGroupInterface;
use FL\QBJSParser\Parser\Doctrine\DoctrineParser;
use FL\QBJSParser\Tests\Util\Doctrine\Mock\DoctrineParser\MockBadEntity2DoctrineParser;
use FL\QBJSParser\Tests\Util\Doctrine\Mock\DoctrineParser\MockBadEntityDoctrineParser;
use FL\QBJSParser\Tests\Util\Doctrine\Mock\DoctrineParser\MockEntityDoctrineParser;
use FL\QBJSParser\Tests\Util\Doctrine\Mock\DoctrineParser\MockEntityWithAssociationDoctrineParser;
use FL\QBJSParser\Tests\Util\Doctrine\Mock\DoctrineParser\MockEntityWithEmbeddableDoctrineParser;
use FL\QBJSParser\Tests\Util\Doctrine\Mock\Entity\MockEntity;
use FL\QBJSParser\Tests\Util\Doctrine\Test\DoctrineParserTestCase;
use PHPUnit\Framework\TestCase;

class DoctrineParserTest extends TestCase
{
    /**
     * @var DoctrineParserTestCase[]
     */
    private $mockEntityParseCases = [];

    /**
     * @var DoctrineParserTestCase[]
     */
    private $mockAssociationParseCases = [];

    /**
     * @var DoctrineParserTestCase[]
     */
    private $mockEmbeddableParseCases = [];

    public function setUp()
    {
        $this->setUpFirstMockEntityParseCases();
        $this->setUpSecondMockEntityParseCases();
        $this->setUpThirdMockEntityParseCases();
        $this->setUpFourthMockEntityParseCases();
        $this->setUpFifthMockEntityParseCases();
        $this->setUpMockAssociationParseCases();
        $this->setUpMockEmbeddableParseCases();
    }

    private function setUpFirstMockEntityParseCases()
    {
        $ruleGroupA = new RuleGroup(RuleGroupInterface::MODE_AND);
        $ruleGroupA_RuleA = new Rule('rule_id', 'price', 'double', 'is_not_null', null);
        $ruleGroupA->addRule($ruleGroupA_RuleA);

        $this->mockEntityParseCases[] = new DoctrineParserTestCase(
            new MockEntityDoctrineParser(),
            $ruleGroupA,
            [
                'price' => 'ASC',
            ],
            'SELECT object FROM '.MockEntity::class.' object WHERE ( object.price IS NOT NULL ) '.
            'ORDER BY object.price ASC ',
            []
        );

        // no where clause
        $ruleGroupB = new RuleGroup(RuleGroupInterface::MODE_AND);
        $this->mockEntityParseCases[] = new DoctrineParserTestCase(
            new MockEntityDoctrineParser(),
            $ruleGroupB,
            [
                'date' => 'ASC',
            ],
            'SELECT object FROM '.MockEntity::class.' object '.
            'ORDER BY object.date ASC ',
            []
        );
    }

    private function setUpSecondMockEntityParseCases()
    {
        $ruleGroupA = new RuleGroup(RuleGroupInterface::MODE_OR);
        $ruleGroupA_RuleA = new Rule('rule_id', 'price', 'double', 'is_not_null', null);
        $ruleGroupA_RuleB = new Rule('rule_id', 'name', 'string', 'equal', 'hello');
        $ruleGroupA_RuleC = new Rule('rule_id', 'name', 'string', 'contains', 'world');
        $ruleGroupA_RuleD = new Rule('rule_id', 'name', 'string', 'not_contains', 'world');
        $ruleGroupA_RuleE = new Rule('rule_id', 'name', 'string', 'begins_with', 'world');
        $ruleGroupA_RuleF = new Rule('rule_id', 'name', 'string', 'ends_with', 'world');
        $ruleGroupA_RuleG = new Rule('rule_id', 'name', 'string', 'not_begins_with', 'world');
        $ruleGroupA_RuleH = new Rule('rule_id', 'name', 'string', 'not_ends_with', 'world');
        $ruleGroupA_RuleI = new Rule('rule_id', 'name', 'string', 'is_empty', null);
        $ruleGroupA_RuleJ = new Rule('rule_id', 'name', 'string', 'is_not_empty', null);
        $ruleGroupA->addRule($ruleGroupA_RuleA);
        $ruleGroupA->addRule($ruleGroupA_RuleB);
        $ruleGroupA->addRule($ruleGroupA_RuleC);
        $ruleGroupA->addRule($ruleGroupA_RuleD);
        $ruleGroupA->addRule($ruleGroupA_RuleE);
        $ruleGroupA->addRule($ruleGroupA_RuleF);
        $ruleGroupA->addRule($ruleGroupA_RuleG);
        $ruleGroupA->addRule($ruleGroupA_RuleH);
        $ruleGroupA->addRule($ruleGroupA_RuleI);
        $ruleGroupA->addRule($ruleGroupA_RuleJ);

        $this->mockEntityParseCases[] = new DoctrineParserTestCase(
            new MockEntityDoctrineParser(),
            $ruleGroupA,
            [
                'price' => 'ASC',
                'name' => 'DESC',
            ],
            sprintf(
                'SELECT object FROM %s object '.
                'WHERE ( object.price IS NOT NULL OR object.name = ?0'.
                ' OR object.name LIKE ?1 OR object.name NOT LIKE ?2 '.
                'OR object.name LIKE ?3 OR object.name LIKE ?4 '.
                'OR object.name NOT LIKE ?5 OR object.name NOT LIKE ?6 '.
                'OR object.name = \'\' OR object.name != \'\' ) '.
                'ORDER BY object.price ASC, object.name DESC ',
                MockEntity::class
            ),
            [
                'hello',
                '%world%',
                '%world%',
                'world%',
                '%world',
                'world%',
                '%world',
            ]
        );
    }

    private function setUpThirdMockEntityParseCases()
    {
        $ruleGroupA = new RuleGroup(RuleGroupInterface::MODE_AND);
        $ruleGroupA_RuleA = new Rule('rule_id', 'price', 'double', 'is_not_null', null);
        $ruleGroupA_RuleB = new Rule('rule_id', 'name', 'string', 'equal', 'hello');
        $ruleGroupA
            ->addRule($ruleGroupA_RuleA)
            ->addRule($ruleGroupA_RuleB)
        ;

        $ruleGroupA_RuleGroup1 = new RuleGroup(RuleGroupInterface::MODE_OR);
        $ruleGroupA_RuleGroup1_RuleA = new Rule('rule_id', 'price', 'double', 'greater', 0.3);
        $ruleGroupA_RuleGroup1_RuleB = new Rule('rule_id', 'price', 'double', 'less_or_equal', 22.0);
        $ruleGroupA->addRuleGroup($ruleGroupA_RuleGroup1);
        $ruleGroupA_RuleGroup1
            ->addRule($ruleGroupA_RuleGroup1_RuleA)
            ->addRule($ruleGroupA_RuleGroup1_RuleB)
        ;

        $this->mockEntityParseCases[] = new DoctrineParserTestCase(
            new MockEntityDoctrineParser(),
            $ruleGroupA,
            [
                'name' => 'DESC',
                'price' => 'ASC',
            ],
            sprintf(
                'SELECT object FROM %s object '.
                'WHERE ( object.price IS NOT NULL AND object.name = ?0 '.
                'AND ( object.price > ?1 OR object.price <= ?2 ) ) '.
                'ORDER BY object.name DESC, object.price ASC ',
                MockEntity::class
            ),
            ['hello', 0.3, 22.0]
        );
    }

    private function setUpFourthMockEntityParseCases()
    {
        $ruleGroupA = new RuleGroup(RuleGroupInterface::MODE_AND, true);
        $ruleGroupA_RuleA = new Rule('rule_id', 'price', 'double', 'is_not_null', null);
        $ruleGroupA_RuleB = new Rule('rule_id', 'name', 'string', 'equal', 'hello');
        $ruleGroupA
            ->addRule($ruleGroupA_RuleA)
            ->addRule($ruleGroupA_RuleB)
        ;

        $ruleGroupA_RuleGroup1 = new RuleGroup(RuleGroupInterface::MODE_OR, true);
        $ruleGroupA_RuleGroup1_RuleA = new Rule('rule_id', 'price', 'double', 'greater', 0.3);
        $ruleGroupA_RuleGroup1_RuleB = new Rule('rule_id', 'price', 'double', 'less_or_equal', 22.0);
        $ruleGroupA->addRuleGroup($ruleGroupA_RuleGroup1);
        $ruleGroupA_RuleGroup1
            ->addRule($ruleGroupA_RuleGroup1_RuleA)
            ->addRule($ruleGroupA_RuleGroup1_RuleB)
        ;

        $this->mockEntityParseCases[] = new DoctrineParserTestCase(
            new MockEntityDoctrineParser(),
            $ruleGroupA,
            [
                'name' => 'DESC',
                'price' => 'ASC',
            ],
            sprintf(
                'SELECT object FROM %s object '.
                'WHERE ( NOT ( object.price IS NOT NULL AND object.name = ?0 '.
                'AND ( NOT ( object.price > ?1 OR object.price <= ?2 ) ) ) ) '.
                'ORDER BY object.name DESC, object.price ASC ',
                MockEntity::class
            ),
            ['hello', 0.3, 22.0]
        );
    }

    private function setUpFifthMockEntityParseCases()
    {
        $ruleGroupA = new RuleGroup(RuleGroupInterface::MODE_AND, false);
        $ruleGroupA_RuleA = new Rule('rule_id', 'price', 'double', 'is_not_null', null);
        $ruleGroupA_RuleB = new Rule('rule_id', 'name', 'string', 'equal', 'hello');
        $ruleGroupA
            ->addRule($ruleGroupA_RuleA)
            ->addRule($ruleGroupA_RuleB)
        ;

        $ruleGroupA_RuleGroup1 = new RuleGroup(RuleGroupInterface::MODE_OR, true);
        $ruleGroupA_RuleGroup1_RuleA = new Rule('rule_id', 'price', 'double', 'greater', 0.3);
        $ruleGroupA_RuleGroup1_RuleB = new Rule('rule_id', 'price', 'double', 'less_or_equal', 22.0);
        $ruleGroupA->addRuleGroup($ruleGroupA_RuleGroup1);
        $ruleGroupA_RuleGroup1
            ->addRule($ruleGroupA_RuleGroup1_RuleA)
            ->addRule($ruleGroupA_RuleGroup1_RuleB)
        ;

        $this->mockEntityParseCases[] = new DoctrineParserTestCase(
            new MockEntityDoctrineParser(),
            $ruleGroupA,
            [
                'name' => 'DESC',
                'price' => 'ASC',
            ],
            sprintf(
                'SELECT object FROM %s object '.
                'WHERE ( object.price IS NOT NULL AND object.name = ?0 '.
                'AND ( NOT ( object.price > ?1 OR object.price <= ?2 ) ) ) '.
                'ORDER BY object.name DESC, object.price ASC ',
                MockEntity::class
            ),
            ['hello', 0.3, 22.0]
        );
    }

    private function setUpMockAssociationParseCases()
    {
        $ruleGroupA = new RuleGroup(RuleGroupInterface::MODE_AND);
        $ruleGroupA_RuleA = new Rule('rule_id', 'price', 'double', 'is_not_null', null);
        $ruleGroupA_RuleB = new Rule('rule_id', 'associationEntity.id', 'string', 'equal', 'hello');
        $ruleGroupA
            ->addRule($ruleGroupA_RuleA)
            ->addRule($ruleGroupA_RuleB)
        ;

        $this->mockAssociationParseCases[] = new DoctrineParserTestCase(
            new MockEntityWithAssociationDoctrineParser(),
            $ruleGroupA,
            [
                'name' => 'DESC',
                'associationEntity.id' => 'ASC',
            ],
            sprintf(
                'SELECT object, object_associationEntity FROM %s object '.
                'LEFT JOIN object.associationEntity object_associationEntity '.
                'WHERE ( object.price IS NOT NULL AND object_associationEntity.id = ?0 ) '.
                'ORDER BY object.name DESC, object_associationEntity.id ASC ',
                MockEntity::class
            ),
            ['hello']
        );
    }

    private function setUpMockEmbeddableParseCases()
    {
        $dateA = new \DateTimeImmutable('now - 2 days');
        $dateB = new \DateTimeImmutable('now + 2 days');
        $ruleGroupA = new RuleGroup(RuleGroupInterface::MODE_AND);
        $ruleGroupA_RuleA = new Rule('rule_id', 'price', 'double', 'is_not_null', null);
        $ruleGroupA_RuleB = new Rule('rule_id', 'associationEntity.id', 'string', 'equal', 'hello');
        $ruleGroupA_RuleC = new Rule('rule_id', 'embeddable.startDate', 'date', 'equal', $dateA);
        $ruleGroupA_RuleD = new Rule('rule_id', 'associationEntity.embeddable.startDate', 'date', 'equal', $dateA);
        $ruleGroupA_RuleE = new Rule('rule_id', 'associationEntity.embeddable.endDate', 'date', 'equal', $dateB);
        $ruleGroupA_RuleF = new Rule('rule_id', 'associationEntity.associationEntity.embeddable.startDate', 'date', 'equal', $dateA);
        $ruleGroupA_RuleG = new Rule('rule_id', 'embeddable.embeddableInsideEmbeddable.code', 'string', 'equal', 'goodbye');
        $ruleGroupA_RuleH = new Rule('rule_id', 'associationEntity.embeddable.embeddableInsideEmbeddable.code', 'string', 'equal', 'cool');
        $ruleGroupA
            ->addRule($ruleGroupA_RuleA)
            ->addRule($ruleGroupA_RuleB)
            ->addRule($ruleGroupA_RuleC)
            ->addRule($ruleGroupA_RuleD)
            ->addRule($ruleGroupA_RuleE)
            ->addRule($ruleGroupA_RuleF)
            ->addRule($ruleGroupA_RuleG)
            ->addRule($ruleGroupA_RuleH)
        ;

        $this->mockEmbeddableParseCases[] = new DoctrineParserTestCase(
            new MockEntityWithEmbeddableDoctrineParser(),
            $ruleGroupA,
            [
                'name' => 'DESC',
                'associationEntity.id' => 'ASC',
                'associationEntity.embeddable.startDate' => 'ASC',
                'associationEntity.associationEntity.embeddable.startDate' => 'DESC',
                'embeddable.embeddableInsideEmbeddable.code' => 'ASC',
                'associationEntity.embeddable.embeddableInsideEmbeddable.code' => 'DESC',
            ],
            sprintf(
                'SELECT object, object_associationEntity FROM %s object '.
                'LEFT JOIN object.associationEntity object_associationEntity '.
                'WHERE ( object.price IS NOT NULL AND object_associationEntity.id = ?0 '.
                'AND object.embeddable.startDate = ?1 AND object_associationEntity.embeddable.startDate = ?2 '.
                'AND object_associationEntity.embeddable.endDate = ?3 '.
                'AND object_associationEntity_associationEntity.embeddable.startDate = ?4 '.
                'AND object.embeddable.embeddableInsideEmbeddable.code = ?5 '.
                'AND object_associationEntity.embeddable.embeddableInsideEmbeddable.code = ?6 ) '.
                'ORDER BY object.name DESC, object_associationEntity.id ASC, '.
                'object_associationEntity.embeddable.startDate ASC, '.
                'object_associationEntity_associationEntity.embeddable.startDate DESC, '.
                'object.embeddable.embeddableInsideEmbeddable.code ASC, '.
                'object_associationEntity.embeddable.embeddableInsideEmbeddable.code DESC ',
                MockEntity::class
            ),
            ['hello', $dateA, $dateA, $dateB, $dateA, 'goodbye', 'cool']
        );
    }

    /**
     * @var DoctrineParserTestCase[]
     */
    private function verifyParserTestCases(array $cases)
    {
        foreach ($cases as $case) {
            $parsed = $case->getDoctrineParser()->parse($case->getRuleGroup(), $case->getSortColumns());

            $dqlString = $parsed->getQueryString();
            $parameters = $parsed->getParameters();

            $this->assertEquals($case->getExpectedDqlString(), $dqlString);
            $this->assertEquals($case->getExpectedParameters(), $parameters);
        }
    }

    /**
     * @test
     */
    public function testMockEntityParseCases()
    {
        $this->verifyParserTestCases($this->mockEntityParseCases);
    }

    /**
     * @test
     */
    public function testMockAssociationParseCases()
    {
        $this->verifyParserTestCases($this->mockAssociationParseCases);
    }

    /**
     * @test
     */
    public function testMockEmbeddableParseCases()
    {
        $this->verifyParserTestCases($this->mockEmbeddableParseCases);
    }

    /**
     * @test
     * @expectedException \FL\QBJSParser\Exception\Parser\Doctrine\FieldMappingException
     */
    public function testMockBadEntity()
    {
        new MockBadEntityDoctrineParser();
    }

    /**
     * @test
     * @expectedException \FL\QBJSParser\Exception\Parser\Doctrine\FieldMappingException
     */
    public function testMockBadEntity2()
    {
        new MockBadEntity2DoctrineParser();
    }

    /**
     * @test
     * @expectedException \FL\QBJSParser\Exception\Parser\Doctrine\InvalidClassNameException
     */
    public function testNonExistentClass()
    {
        new DoctrineParser('This_Really_Long_Class_Name_With_Invalid_Characters_@#_IS_NOT_A_CLASS', [], []);
    }
}
