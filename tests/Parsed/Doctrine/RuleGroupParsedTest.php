<?php

namespace FL\QBJSParser\Tests\Parsed\Doctrine;

use FL\QBJSParser\Parsed\Doctrine\ParsedRuleGroup;
use FL\QBJSParser\Exception\Parsed\Doctrine\ParsedRuleGroupConstructionException;
use FL\QBJSParser\Tests\Util\Doctrine\Mock\Entity\MockEntity;
use FL\QBJSParser\Tests\Util\Doctrine\Mock\Entity\MockEntityAssociation;

class RuleGroupParsedTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var array
     */
    private $sampleValid_Dqls_Parameters;

    /**
     * @var array
     */
    private $sampleInvalid_Dqls_Parameters;

    public function setUp()
    {
        $this->sampleValid_Dqls_Parameters = [
            ['dqlString' => 'SELECT object FROM SomeNamespace/SomeClass object WHERE object.id IS NOT NULL', 'parameters' => []],
            ['dqlString' => 'SELECT object FROM SomeNamespace/SomeClass object WHERE object.id IS NULL', 'parameters' => []],
            ['dqlString' => 'SELECT object FROM SomeNamespace/SomeClass object WHERE object.id = ?0', 'parameters' => [3]],
            ['dqlString' => 'SELECT object FROM SomeNamespace/SomeClass object WHERE object.id IN (?0)', 'parameters' => [3]],
            ['dqlString' => 'SELECT object FROM SomeNamespace/SomeClass object WHERE object.id IN (?0)', 'parameters' => [[3, null]]],
        ];
        $this->sampleInvalid_Dqls_Parameters = [
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
     * @test
     */
    public function testValidConstructions()
    {
        foreach ($this->sampleValid_Dqls_Parameters as $valid_Dql_Parameter) {
            $dqlString = $valid_Dql_Parameter['dqlString'];
            $parameters = $valid_Dql_Parameter['parameters'];
            $parsedRuleGroup = new ParsedRuleGroup($dqlString, $parameters, MockEntity::class);

            $this->assertEquals($parsedRuleGroup->getQueryString(), $dqlString);
            $this->assertEquals($parsedRuleGroup->getParameters(), $parameters);
            $this->assertEquals($parsedRuleGroup->getClassName(), MockEntity::class);
        }
    }

    /**
     * @test
     */
    public function testInvalidParameterConstructions()
    {
        foreach ($this->sampleInvalid_Dqls_Parameters as $invalid_Dql_Parameter) {
            $dqlString = $invalid_Dql_Parameter['dqlString'];
            $parameters = $invalid_Dql_Parameter['parameters'];
            $this->assertParsedRuleGroupConstructionException(function () use ($dqlString, $parameters) {
                new ParsedRuleGroup($dqlString, $parameters, MockEntity::class);
            }, $invalid_Dql_Parameter['dqlString']);
        }
    }

    /**
     * @test
     */
    public function testInvalidClassNameConstructions()
    {
        foreach ($this->sampleValid_Dqls_Parameters as $valid_Dql_Parameter) {
            $dqlString = $valid_Dql_Parameter['dqlString'];
            $parameters = $valid_Dql_Parameter['parameters'];

            $this->assertParsedRuleGroupConstructionException(function () use ($dqlString, $parameters) {
                new ParsedRuleGroup($dqlString, $parameters, 'ThisNameSpaceDoesNotExist\\ThisClassNameDoesNotExist');
            }, $valid_Dql_Parameter['dqlString']);

            $this->assertParsedRuleGroupConstructionException(function () use ($dqlString, $parameters) {
                new ParsedRuleGroup($dqlString, $parameters, 'ThisClassNameDoesNotExist');
            }, $valid_Dql_Parameter['dqlString']);
        }
    }

    /**
     * @param \Closure $function
     * @param string   $dqlString
     */
    private function assertParsedRuleGroupConstructionException(\Closure $function, string $dqlString)
    {
        try {
            $function();
        } catch (ParsedRuleGroupConstructionException $e) {
            return;
        }
        $this->fail(ParsedRuleGroup::class.' with dqlString: \''.$dqlString.'\' should be invalid.');
    }

    /**
     * @test
     */
    public function testCopyWithReplacedString()
    {
        $withOrderBy = new ParsedRuleGroup(
            sprintf('SELECT object FROM %s object WHERE object.id != 3 ORDER BY object.id', MockEntity::class),
            [],
            MockEntity::class
        );

        $withGroupByWithOrderBy = $withOrderBy->copyWithReplacedString('ORDER BY', 'GROUP BY object.id ORDER BY', 'GROUP BY');
        $this->assertEquals(
            sprintf('SELECT object FROM %s object WHERE object.id != 3 GROUP BY object.id ORDER BY object.id', MockEntity::class),
            $withGroupByWithOrderBy->getQueryString()
        );
        $withGroupByWithOrderBy = $withOrderBy->copyWithReplacedString('ORDER BY', 'GROUP BY object.id ORDER BY', ' ---this wont be used-- ');
        $this->assertEquals(
            sprintf('SELECT object FROM %s object WHERE object.id != 3 GROUP BY object.id ORDER BY object.id', MockEntity::class),
            $withGroupByWithOrderBy->getQueryString()
        );

        $withoutOrderBy = new ParsedRuleGroup(
            sprintf('SELECT object FROM %s object WHERE object.id != 3', MockEntity::class),
            [],
            MockEntity::class
        );

        $withGroupByWithoutOrderBy = $withoutOrderBy->copyWithReplacedString('ORDER BY', 'GROUP BY object.id ORDER BY', ' GROUP BY object.id');
        $this->assertEquals(
            sprintf('SELECT object FROM %s object WHERE object.id != 3 GROUP BY object.id', MockEntity::class),
            $withGroupByWithoutOrderBy->getQueryString()
        );
        $withoutGroupByWithoutOrderBy = $withoutOrderBy->copyWithReplacedString('ORDER BY', 'GROUP BY object.id ORDER BY', '');
        $this->assertEquals(
            sprintf('SELECT object FROM %s object WHERE object.id != 3', MockEntity::class),
            $withoutGroupByWithoutOrderBy->getQueryString()
        );
        $withoutGroupByWithoutOrderByWithExtraEnding = $withoutOrderBy->copyWithReplacedString('ORDER BY', 'GROUP BY object.id ORDER BY', ' _extra_ending_ ');
        $this->assertEquals(
            sprintf('SELECT object FROM %s object WHERE object.id != 3 _extra_ending_ ', MockEntity::class),
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
        $this->assertEquals(
            sprintf('SELECT object FROM %s object LEFT JOIN object.associationEntity association WHERE object.id != 3 ORDER BY object.id', MockEntity::class),
            $withOneSelect->getQueryString()
        );

        $withoutOrderBy = new ParsedRuleGroup(
            sprintf('SELECT object FROM %s object WHERE object.id != 3', MockEntity::class),
            [],
            MockEntity::class
        );

        $withGroupByWithoutOrderBy = $withoutOrderBy->copyWithReplacedStringRegex('/ORDER BY/', 'GROUP BY object.id ORDER BY', ' GROUP BY object.id');
        $this->assertEquals(
            sprintf('SELECT object FROM %s object WHERE object.id != 3 GROUP BY object.id', MockEntity::class),
            $withGroupByWithoutOrderBy->getQueryString()
        );
        $withoutGroupByWithoutOrderBy = $withoutOrderBy->copyWithReplacedStringRegex('/ORDER BY/', 'GROUP BY object.id ORDER BY', '');
        $this->assertEquals(
            sprintf('SELECT object FROM %s object WHERE object.id != 3', MockEntity::class),
            $withoutGroupByWithoutOrderBy->getQueryString()
        );
        $withoutGroupByWithoutOrderByWithExtraEnding = $withoutOrderBy->copyWithReplacedStringRegex('/ORDER BY/', 'GROUP BY object.id ORDER BY', ' _extra_ending_ ');
        $this->assertEquals(
            sprintf('SELECT object FROM %s object WHERE object.id != 3 _extra_ending_ ', MockEntity::class),
            $withoutGroupByWithoutOrderByWithExtraEnding->getQueryString()
        );
    }
}
