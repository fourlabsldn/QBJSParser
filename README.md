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
- While we set up packagist, use the repository directly in composer. Add the following to your composer.json
```json
    "repositories": [
        {
            "type":"package",
            "package": {
                "name": "FL/QBJSParser",
                "version":"0.1.1",
                "source": {
                    "url": "https://github.com/fourlabsldn/QBJSParser.git",
                    "type": "git",
                    "reference":"master"
                },
                "autoload": {
                    "psr-4": { "FL\\QBJSParser\\": "src" }
                }
            }
        }
    ],
    "require": {
        "FL/QBJSParser": "^0.1.1",
    },
```
- And then run `composer require FL/QBJSParser` 

## Quick Tour

- `FL\QBJSParser\Serializer\JsonDeserializer::deserialize()` deserializes a JSON string into an instance of `FL\QBJSParser\Model\RuleGroup`
- This `RuleGroup` object can then be parsed into something your ORM/ODM can use, to create a query.
- Abstract Parsers live at `FL\QBJSParser\Parser`, extend these into custom parsers, as explained below.

## Creating Custom Parsers
- [**Doctrine Custom Parsers**](Documentation/Parsers/Doctrine.md)


