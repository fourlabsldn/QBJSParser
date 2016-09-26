<?php

namespace FL\QBJSParser\Serializer;

use FL\QBJSParser\Exception\Serializer\JsonDeserializerInvalidJsonException;
use FL\QBJSParser\Exception\Serializer\JsonDeserializerConditionException;
use FL\QBJSParser\Exception\Serializer\JsonDeserializerRuleKeyException;
use FL\QBJSParser\Model\Rule;
use FL\QBJSParser\Model\RuleGroup;
use FL\QBJSParser\Model\RuleGroupInterface;
use FL\QBJSParser\Model\RuleInterface;

class JsonDeserializer implements DeserializerInterface
{
    /**
     * @inheritdoc
     */
    public function deserialize(string $string) : RuleGroupInterface
    {
        $decodedRuleGroup = json_decode($string, true);
        if (is_null($decodedRuleGroup) || !is_array($decodedRuleGroup)) {
            throw new JsonDeserializerInvalidJsonException();
        }

        return $this->deserializeRuleGroup($decodedRuleGroup);
    }

    /**
     * @param array $decodedRuleGroup
     * @return RuleGroupInterface
     */
    private function deserializeRuleGroup(array $decodedRuleGroup) : RuleGroupInterface
    {
        if (!array_key_exists('condition', $decodedRuleGroup)) {
            throw new JsonDeserializerConditionException('Missing condition in RuleGroup');
        }

        $deserializedRuleGroup = new RuleGroup($decodedRuleGroup['condition']);

        foreach ($decodedRuleGroup['rules'] as $ruleOrGroup) {
            if (array_key_exists('condition', $ruleOrGroup)) {
                $deserializedRuleGroup->addRuleGroup($this->deserializeRuleGroup($ruleOrGroup));
            } elseif (array_key_exists('id', $ruleOrGroup)) {
                $deserializedRuleGroup->addRule($this->deserializeRule($ruleOrGroup));
            }
        }

        return $deserializedRuleGroup;
    }

    /**
     * @param array $decodedRule
     * @return RuleInterface
     * @throws \Exception
     */
    private function deserializeRule(array $decodedRule): RuleInterface
    {
        $missingKey = (
            (!array_key_exists('id', $decodedRule)) ||
            (!array_key_exists('field', $decodedRule)) ||
            (!array_key_exists('type', $decodedRule)) ||
            (!array_key_exists('operator', $decodedRule)) ||
            (!array_key_exists('value', $decodedRule))
        );
        if ($missingKey) {
            $keysGiven = implode(', ', array_keys($decodedRule));
            throw new JsonDeserializerRuleKeyException('Keys given: ' . $keysGiven . '. Expected id, field, type, operator, value');
        }

        $id = $decodedRule['id'];
        $field = $decodedRule['field'];
        $type = $decodedRule['type'];
        $operator = $decodedRule['operator'];
        $value = $decodedRule['value'];

        // operators in and not_in require an array, the next two lines ensure that single values are converted to an array
        if(in_array($operator, ['in', 'not_in']) && !is_array($value)){
            $value = [$value];
        }

        if (!is_array($value)) {
            $value = $this->convertValueAccordingToType($type, $value);
            return new Rule($id, $field, $type, $operator, $value);
        } else {
            $valuesArray = $value;
            foreach ($valuesArray as $key => $value) {
                $valuesArray[$key] = $this->convertValueAccordingToType($type, $value);
            }
            return new Rule($id, $field, $type, $operator, $valuesArray);
        }
    }

    /**
     * @param string $type
     * @param mixed $value
     * @return mixed
     */
    private function convertValueAccordingToType(string $type, $value)
    {
        if (is_null($value) || $value === 'null' || $value === 'NULL') {
            return null; // nulls shouldn't be converted
        }

        switch ($type) { /** @see Rule::$type */
            case 'string':
                return strval($value);
            case 'integer':
                return intval($value);
            case 'double':
                return doubleval($value);
            case 'date':
            case 'time':
            case 'datetime':
                return new \DateTimeImmutable($value);
            case 'boolean':
                return boolval($value);
            default:
                return $value;
        }
    }
}
