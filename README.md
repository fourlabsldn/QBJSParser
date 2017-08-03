# Query Builder JS Parser

[![StyleCI](https://styleci.io/repos/68804319/shield?branch=master)](https://styleci.io/repos/68804319)
[![Build Status](https://travis-ci.org/fourlabsldn/QBJSParser.svg?branch=master)](https://travis-ci.org/fourlabsldn/QBJSParser)
[![Coverage Status](https://coveralls.io/repos/github/fourlabsldn/QBJSParser/badge.svg?branch=master)](https://coveralls.io/github/fourlabsldn/QBJSParser?branch=master)
[![License](https://poser.pugx.org/fourlabs/qbjs-parser/license)](https://packagist.org/packages/fourlabs/qbjs-parser)
[![Total Downloads](https://poser.pugx.org/fourlabs/qbjs-parser/downloads)](https://packagist.org/packages/fourlabs/qbjs-parser)

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
- Parsers live at `FL\QBJSParser\Parser`.

## Available Parsers
- [**Doctrine Parser**](Documentation/Parsers/Doctrine.md)


## Tests

To run the test suite, you need [composer](http://getcomposer.org).

```bash
    $ composer install
    $ phpunit
```

## License

QBJSParser is licensed under the MIT license.


