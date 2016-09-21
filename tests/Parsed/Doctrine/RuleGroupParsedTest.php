<?php

namespace FL\QBJSParser\Tests\Parsed\Doctrine;

use FL\QBJSParser\Parsed\Doctrine\ParsedRuleGroup;
use FL\QBJSParser\Exception\Parsed\Doctrine\ParsedRuleGroupConstructionException;

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
            ['dqlString' => 'SELECT object FROM SomeNamespace/SomeClass object WHERE object.id IS NULL', 'parameters' => [new \DateTime()]],
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
            $parsedRuleGroup = new ParsedRuleGroup($dqlString, $parameters);

            $this->assertEquals($parsedRuleGroup->getDqlString(), $dqlString);
            $this->assertEquals($parsedRuleGroup->getParameters(), $parameters);
        }
    }

    /**
     * @test
     */
    public function testInvalidConstructions()
    {
        foreach ($this->sampleInvalid_Dqls_Parameters as $invalid_Dql_Parameter) {
            $dqlString = $invalid_Dql_Parameter['dqlString'];
            $parameters = $invalid_Dql_Parameter['parameters'];
            $this->assertParsedRuleGroupConstructionException(function () use ($dqlString, $parameters) {
                new ParsedRuleGroup($dqlString, $parameters);
            }, $invalid_Dql_Parameter['dqlString']);
        }
    }

    /**
     * @param \Closure $function
     * @param string $dqlString
     */
    private function assertParsedRuleGroupConstructionException(\Closure $function, string $dqlString)
    {
        try {
            $function();
        } catch (ParsedRuleGroupConstructionException $e) {
            return;
        }
        $this->fail(ParsedRuleGroup::class . ' with dqlString: \'' . $dqlString . '\' should be invalid.');
    }
}
