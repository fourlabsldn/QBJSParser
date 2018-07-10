<?php

namespace FL\QBJSParser\Tests\Parsed\Doctrine;

use FL\QBJSParser\Parsed\Doctrine\ParsedRuleGroup;
use FL\QBJSParser\Exception\Parsed\Doctrine\ParsedRuleGroupConstructionException;
use FL\QBJSParser\Tests\Util\Doctrine\Mock\Entity\MockEntity;
use FL\QBJSParser\Tests\Util\Doctrine\Mock\Entity\MockEntityAssociation;
use PHPUnit\Framework\TestCase;

class RuleGroupParsedTest extends TestCase
{
    /**
     * @dataProvider validDqlProvider
     *
     * @param string $dql
     * @param array  $parameters
     */
    public function testValidConstructions(string $dql, array $parameters)
    {
        $parsedRuleGroup = new ParsedRuleGroup($dql, $parameters, MockEntity::class);

        self::assertEquals($parsedRuleGroup->getQueryString(), $dql);
        self::assertEquals($parsedRuleGroup->getParameters(), $parameters);
        self::assertEquals($parsedRuleGroup->getClassName(), MockEntity::class);
    }

    public function validDqlProvider()
    {
        return [
            ['dqlString' => 'SELECT object FROM SomeNamespace/SomeClass object WHERE object.id IS NOT NULL', 'parameters' => []],
            ['dqlString' => 'SELECT object FROM SomeNamespace/SomeClass object WHERE object.id IS NULL', 'parameters' => []],
            ['dqlString' => 'SELECT object FROM SomeNamespace/SomeClass object WHERE object.id = ?0', 'parameters' => [3]],
            ['dqlString' => 'SELECT object FROM SomeNamespace/SomeClass object WHERE object.id IN (?0)', 'parameters' => [3]],
            ['dqlString' => 'SELECT object FROM SomeNamespace/SomeClass object WHERE object.id IN (?0)', 'parameters' => [[3, null]]],
        ];
    }

    /**
     * @dataProvider invalidDqlProvider
     *
     * @param string $dql
     * @param array  $parameters
     */
    public function testInvalidParameterConstructions(string $dql, array $parameters)
    {
        $this->expectException(ParsedRuleGroupConstructionException::class);
        new ParsedRuleGroup($dql, $parameters, MockEntity::class);
    }

    public function invalidDqlProvider()
    {
        return [
            // number of parameters doesn't match
            ['dqlString' => 'SELECT object FROM SomeNamespace/SomeClass object WHERE object.id IS NULL', 'parameters' => [1]],
            // number of parameters doesn't match
            ['dqlString' => 'SELECT object FROM SomeNamespace/SomeClass object WHERE object.id IS NULL', 'parameters' => [new \DateTimeImmutable()]],
            // number of parameters doesn't match
            ['dqlString' => 'SELECT object FROM SomeNamespace/SomeClass object WHERE object.id BETWEEN ?0 AND ?1', 'parameters' => [3, 2, 5]],
            // two parameters given, expected one array
            ['dqlString' => 'SELECT object FROM SomeNamespace/SomeClass object WHERE object.id IN (?0)', 'parameters' => [3, null]],
        ];
    }

    /**
     * @dataProvider validDqlProvider
     *
     * @param string $dql
     * @param array  $parameters
     */
    public function testInvalidClassNameConstructions(string $dql, array $parameters)
    {
        $this->expectException(ParsedRuleGroupConstructionException::class);
        new ParsedRuleGroup($dql, $parameters, 'ThisNameSpaceDoesNotExist\\ThisClassNameDoesNotExist');

        $this->expectException(ParsedRuleGroupConstructionException::class);
        new ParsedRuleGroup($dql, $parameters, 'ThisClassNameDoesNotExist');
    }

    public function testCopyWithReplacedString()
    {
        $withOrderBy = new ParsedRuleGroup(
            sprintf('SELECT object FROM %s object WHERE object.id != 3 ORDER BY object.id', MockEntity::class),
            [],
            MockEntity::class
        );

        $withGroupByWithOrderBy = $withOrderBy->copyWithReplacedString('ORDER BY', 'GROUP BY object.id ORDER BY', 'GROUP BY');
        self::assertEquals(
            sprintf('SELECT object FROM %s object WHERE object.id != 3 GROUP BY object.id ORDER BY object.id', MockEntity::class),
            $withGroupByWithOrderBy->getQueryString()
        );
        $withGroupByWithOrderBy = $withOrderBy->copyWithReplacedString('ORDER BY', 'GROUP BY object.id ORDER BY', ' ---this wont be used-- ');
        self::assertEquals(
            sprintf('SELECT object FROM %s object WHERE object.id != 3 GROUP BY object.id ORDER BY object.id', MockEntity::class),
            $withGroupByWithOrderBy->getQueryString()
        );

        $withoutOrderBy = new ParsedRuleGroup(
            sprintf('SELECT object FROM %s object WHERE object.id != 3', MockEntity::class),
            [],
            MockEntity::class
        );

        $withGroupByWithoutOrderBy = $withoutOrderBy->copyWithReplacedString('ORDER BY', 'GROUP BY object.id ORDER BY', ' GROUP BY object.id');
        self::assertEquals(
            sprintf('SELECT object FROM %s object WHERE object.id != 3 GROUP BY object.id', MockEntity::class),
            $withGroupByWithoutOrderBy->getQueryString()
        );
        $withoutGroupByWithoutOrderBy = $withoutOrderBy->copyWithReplacedString('ORDER BY', 'GROUP BY object.id ORDER BY', '');
        self::assertEquals(
            sprintf('SELECT object FROM %s object WHERE object.id != 3', MockEntity::class),
            $withoutGroupByWithoutOrderBy->getQueryString()
        );
        $withoutGroupByWithoutOrderByWithExtraEnding = $withoutOrderBy->copyWithReplacedString('ORDER BY', 'GROUP BY object.id ORDER BY', ' _extra_ending_');
        self::assertEquals(
            sprintf('SELECT object FROM %s object WHERE object.id != 3 _extra_ending_', MockEntity::class),
            $withoutGroupByWithoutOrderByWithExtraEnding->getQueryString()
        );
    }

    /**
     * @test
     */
    public function testCopyWithReplacedStringRegex()
    {
        $withMultipleSelects = new ParsedRuleGroup(
            sprintf('SELECT object, association, association_in_association FROM %s object LEFT JOIN object.associationEntity association WHERE object.id != 3 ORDER BY object.id', MockEntity::class),
            [],
            MockEntityAssociation::class
        );
        $withOneSelect = $withMultipleSelects->copyWithReplacedStringRegex('/SELECT.+object.+FROM/', 'SELECT object FROM', '');
        self::assertEquals(
            sprintf('SELECT object FROM %s object LEFT JOIN object.associationEntity association WHERE object.id != 3 ORDER BY object.id', MockEntity::class),
            $withOneSelect->getQueryString()
        );

        $withoutOrderBy = new ParsedRuleGroup(
            sprintf('SELECT object FROM %s object WHERE object.id != 3', MockEntity::class),
            [],
            MockEntity::class
        );

        $withGroupByWithoutOrderBy = $withoutOrderBy->copyWithReplacedStringRegex('/ORDER BY/', 'GROUP BY object.id ORDER BY', ' GROUP BY object.id');
        self::assertEquals(
            sprintf('SELECT object FROM %s object WHERE object.id != 3 GROUP BY object.id', MockEntity::class),
            $withGroupByWithoutOrderBy->getQueryString()
        );
        $withoutGroupByWithoutOrderBy = $withoutOrderBy->copyWithReplacedStringRegex('/ORDER BY/', 'GROUP BY object.id ORDER BY', '');
        self::assertEquals(
            sprintf('SELECT object FROM %s object WHERE object.id != 3', MockEntity::class),
            $withoutGroupByWithoutOrderBy->getQueryString()
        );
        $withoutGroupByWithoutOrderByWithExtraEnding = $withoutOrderBy->copyWithReplacedStringRegex('/ORDER BY/', 'GROUP BY object.id ORDER BY', ' _extra_ending_');
        self::assertEquals(
            sprintf('SELECT object FROM %s object WHERE object.id != 3 _extra_ending_', MockEntity::class),
            $withoutGroupByWithoutOrderByWithExtraEnding->getQueryString()
        );
    }
}
