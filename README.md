# Query Builder JS Parser

[![Build Status](https://travis-ci.org/fourlabsldn/QBJSParser.svg?branch=master)](https://travis-ci.org/fourlabsldn/QBJSParser)

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

```bash
    $ composer require fourlabs/qbjs-parser
```

## Quick Tour

- `FL\QBJSParser\Serializer\JsonDeserializer::deserialize()` deserializes a JSON string into an instance of `FL\QBJSParser\Model\RuleGroup`
- This `RuleGroup` object can then be parsed into something your ORM/ODM can use, to create a query.
- Abstract Parsers live at `FL\QBJSParser\Parser`, extend these into custom parsers, as explained below.

## Creating Custom Parsers
- [**Doctrine Custom Parsers**](Documentation/Parsers/Doctrine.md)


## Tests

To run the test suite, you need [composer](http://getcomposer.org).

```bash
    $ composer install
    $ phpunit
```

## License

QBJSParser is licensed under the MIT license.


