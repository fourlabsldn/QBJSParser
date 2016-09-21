# Query Builder JS Parser

Parse JSON coming from [jQuery QueryBuilder](http://querybuilder.js.org/), such as

```json
{
   "condition": "AND",
   "rules": [
     {
       "id": "price",
       "field": "price",
       "type": "double",
       "input": "text",
       "operator": "less",
       "value": "10.25"
     }
   ]
 }
```

## Installation
- `composer require`

## Quick Tour

- `FL\QBJSParser\Serializer\JsonDeserializer::deserialize()` deserializes a JSON string into an instance of `FL\QBJSParser\Model\RuleGroup`
- This `RuleGroup` object can then be parsed into something your ORM/ODM can use, to create a query.
- Abstract Parsers live at `FL\QBJSParser\Parser`, extend these into custom parsers, as explained below.

## Creating Custom Parsers
- [**Doctrine Custom Parsers**](Documentation/Parsers/Doctrine.md)


