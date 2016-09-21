# Doctrine Custom Parsers

#### What are they?
- Doctrine Custom Parsers implement `FL\QBJSParser\ParserInterface` and can extend `FL\QBJSParser\Doctrine\AbstractDoctrineParser`.
- Doctrine Custom Parsers parse `FL\QBJSParser\Model\RuleGroup` into a `FL\QBJSParser\Parsed\Doctrine\ParsedRuleGroup`.
- A `ParsedRuleGroup` has two properties accessible via getters: `$dqlString` and `$parameters`. 
- Use the `ParsedRuleGroup` to create a Doctrine Query with `Doctrine\ORM\EntityManager::createQuery($dql)` and `Doctrine\ORM\Query::setParameters($parameters)`

#### Usage
- Each Doctrine entity will need a corresponding Doctrine Custom Parser.
- Your Doctrine Custom Parser should call `parent::construct($className)` with the class name of its corresponding Doctrine entity.
- Your Doctrine Custom Parser should override `parent::map_QueryBuilderFields_ToEntityProperties` according to the properties you wish to enable searching for in your entity.

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

namespace YourNamespace\YourApp\QBJSParsers;

use YourNamespace\YourApp\Entity\Product;
use FL\QBJSParser\Parser\Doctrine\AbstractDoctrineParser;

class ProductParser extends AbstractDoctrineParser
{
    public function __construct()
    {
        parent::__construct(Product::class);
    }

    /**
     * @return array
     */
    protected function map_QueryBuilderFields_ToEntityProperties() : array
    {
        // only allow searches by id and price
        return [
            'id' => 'id',
            'price' => 'price',
        ];
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
use YourNamespace\YourApp\QBJSParser\ProductParser;

//...
    $jsonDeserializer = new JsonDeserializer();
    $parser = new ProductParser();
    
    $deserializedRuleGroup = $jsonDeserializer->deserialize($jsonString);
    $parsedRuleGroup = $parser->parse($deserializedRuleGroup);
    
    /** @var EntityManagerInterface $entityManager */
    $query = $entityManager->createQuery($parsedRuleGroup->getDqlString());
    $query->setParameters($parsedRuleGroup->getParameters());
    $results = $query->execute();
//... 
```

