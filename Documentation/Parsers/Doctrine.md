# Doctrine Custom Parser

#### What is it?
- A Doctrine Custom Parser implements `FL\QBJSParser\ParserInterface` and extends `FL\QBJSParser\Doctrine\AbstractDoctrineParser`.
- A Doctrine Custom Parser parses `FL\QBJSParser\Model\RuleGroup` into a `FL\QBJSParser\Parsed\Doctrine\ParsedRuleGroup`.
- A `ParsedRuleGroup` has two properties accessible via getters: `$dqlString` and `$parameters`. 
- Use the `ParsedRuleGroup` to create a Doctrine Query with `Doctrine\ORM\EntityManager::createQuery($dql)` and `Doctrine\ORM\Query::setParameters($parameters)`

#### Usage
- Create one `DoctrineCustomParser` by extending `FL\QBJSParser\Doctrine\AbstractDoctrineParser`.
- When you are parsing a `$jsonString` for a particular Doctrine Entity, create an instance of `DoctrineCustomParser`
    - Your instance should call `parent::__construct` with the class name of its corresponding Doctrine entity.
    - Your instance should call `parent::__construct` with an array `$queryBuilderFieldsToEntityProperties` according to the properties you wish to enable searching for, in your entity.

## Example

#### Step One
Create a Doctrine entity, such as this one,

```php
<?php

namespace YourNamespace\YourApp\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 */
class Product
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer", nullable=false)
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\Column(type="string", nullable=false)
     * @var string
     */
    private $name;

    /**
     * @ORM\Column(type="decimal", nullable=true, scale=2, precision=10)
     * @var string|null
     */
    private $price;

    //...
}
```

#### Step Two
Create a corresponding Doctrine Custom Parser class such as this one, 

```php
<?php

namespace YourNamespace\YourApp\QBJSParser;

use FL\QBJSParser\Parser\Doctrine\AbstractDoctrineParser;

class DoctrineCustomParser extends AbstractDoctrineParser
{
    public function __construct(string $className, array $queryBuilderFieldsToEntityProperties)
    {
        parent::__construct($className, $queryBuilderFieldsToEntityProperties);
    }
}
```

#### Step Three
Finally, assuming you have an `$entityManager`, and a  valid `$jsonString` created by [jQuery QueryBuilder](http://querybuilder.js.org/), you are ready to create a Doctrine query, and get results!

```php
<?php

namespace YourNamespace\YourApp\Controller;

use Doctrine\ORM\EntityManagerInterface;
use FL\QBJSParser\Serializer\JsonDeserializer;
use YourNamespace\YourApp\QBJSParser\DoctrineCustomParser;
use YourNamespace\YourApp\Entity\Product;

//...
    $jsonDeserializer = new JsonDeserializer();
    $parser = new DoctrineCustomParser(Product::class, [
        'id'=>'id',
        'name'=>'name',
        'price'=>'price',
    ]);
    
    $deserializedRuleGroup = $jsonDeserializer->deserialize($jsonString);
    $parsedRuleGroup = $parser->parse($deserializedRuleGroup);
    
    /** @var EntityManagerInterface $entityManager */
    $query = $entityManager->createQuery($parsedRuleGroup->getDqlString());
    $query->setParameters($parsedRuleGroup->getParameters());
    $results = $query->execute();
//... 
```

