# Doctrine Parser

#### What is it?
- `FL\QBJSParser\Doctrine\AbstractDoctrineParser` parses `FL\QBJSParser\Model\RuleGroup` into a `FL\QBJSParser\Parsed\Doctrine\ParsedRuleGroup`.
- A `ParsedRuleGroup` has two properties accessible via getters: `$dqlString` and `$parameters`. 
- Use the `ParsedRuleGroup` to create a Doctrine Query with `Doctrine\ORM\EntityManager::createQuery($dql)` and `Doctrine\ORM\Query::setParameters($parameters)`

#### Usage
- When you are parsing a `$jsonString` for a particular Doctrine Entity, create an instance of `DoctrineParser`.
- Don't forget to construct this instance with the `$classname` of the Doctrine Entity, the `$queryBuilderFieldsToEntityProperties`, and `$queryBuilderFieldPrefixesToAssociationClasses`.

## Example

Suppose you have a Doctrine entity, such as this one,

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
    
    /**
     * @ORM\ManyToMany(targetEntity="Label")
     * @var string|null
     */
    private $labels;

    //...
}
```

And its `$labels` association, looks like this

```php
<?php

namespace YourNamespace\YourApp\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 */
class Label
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

    //...
}
```

Assuming you have an `$entityManager`, and a  valid `$jsonString` created by [jQuery QueryBuilder](http://querybuilder.js.org/), you are ready to create a Doctrine query, and get results!

```php
<?php

namespace YourNamespace\YourApp\Controller;

use Doctrine\ORM\EntityManagerInterface;
use FL\QBJSParser\Serializer\JsonDeserializer;
use FL\QBJSParser\Parser\Doctrine\DoctrineParser;
use YourNamespace\YourApp\QBJSParser\DoctrineCustomParser;
use YourNamespace\YourApp\Entity\Product;
use YourNamespace\YourApp\Entity\Label;

//...
    $jsonDeserializer = new JsonDeserializer();
    $productParser = new DoctrineParser(Product::class, 
        [
            'id'=>'id',
            'name'=>'name',
            'price'=>'price',
            'labels.id' => 'labels.id',
            'labels.name' => 'labels.name',
        ],
        [
            'labels'=>Label::class,
        ]
    );
    
    $deserializedRuleGroup = $jsonDeserializer->deserialize($jsonString);
    $parsedRuleGroup = $productParser->parse($deserializedRuleGroup);
    
    /** @var EntityManagerInterface $entityManager */
    $query = $entityManager->createQuery($parsedRuleGroup->getDqlString());
    $query->setParameters($parsedRuleGroup->getParameters());
    $results = $query->execute();
//... 
```

