<?php

namespace Mdiyakov\DoctrineSolrBundle\Tests\Query\Hydrator;

use Doctrine\ORM\EntityRepository;
use Mdiyakov\DoctrineSolrBundle\Query\Hydrator\SelectQueryHydrator;
use Mdiyakov\DoctrineSolrBundle\Schema\Field\ConfigEntityField;
use Mdiyakov\DoctrineSolrBundle\Schema\Field\Entity\IntField;
use Mdiyakov\DoctrineSolrBundle\Schema\Schema;

class Query
{

    private $result;

    public function __construct($result)
    {
        $this->result = $result;
    }


    public function getResult()
    {
        return $this->result;
    }

}

class SelectQueryHydratorTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $entityRepository;


    /**
     * @expectedException \Mdiyakov\DoctrineSolrBundle\Exception\DiscriminatorFieldException
     * @expectedExceptionMessage Discriminator field "d_article" must be presented in dataset
     */
    public function testDiscriminatorValueNotDefined()
    {
        $configFieldName = 'type';
        $configFieldValue = 'article';
        $documentFieldName = 'd_article';
        $hydrator = $this->createHydrator($configFieldName, $configFieldValue, $documentFieldName);

        $hydrator->hydrate([
            ['id' => 1, 'title' => 'string'],
            ['id' => 3, 'title' => 'string2'],
        ]);
    }


    /**
     * @expectedException \Mdiyakov\DoctrineSolrBundle\Exception\DiscriminatorFieldException
     * @expectedExceptionMessage Discriminator field value "type" must be "article" but "page" is provided
     */
    public function testDiscriminatorValueNotCorrect()
    {
        $configFieldName = 'type';
        $configFieldValue = 'article';
        $documentFieldName = 'd_article';
        $hydrator = $this->createHydrator($configFieldName, $configFieldValue, $documentFieldName);

        $hydrator->hydrate([
            ['id' => 1, 'title' => 'string', $documentFieldName => 'page'],
            ['id' => 3, 'title' => 'string2', $documentFieldName => 'page'],
        ]);
    }

    /**
     * @expectedException \Mdiyakov\DoctrineSolrBundle\Exception\HydratorException
     * @expectedExceptionMessage Entities of "ArticleEntity" with "1, 3" primary keys are not found in database
     */
    public function testWrongEntitiesCount()
    {
        $configFieldName = 'type';
        $configFieldValue = 'article';
        $documentFieldName = 'd_article';
        $primaryKeyDocumentName = 'd_id';
        $primaryKeyFieldName = 'id';
        $entityConfigClass = 'ArticleEntity';
        $hydrator = $this->createHydrator($configFieldName, $configFieldValue, $documentFieldName, $primaryKeyDocumentName, $entityConfigClass, $primaryKeyFieldName);
        $queryBuilder = $this->getMockBuilder('Doctrine\ORM\QueryBuilder')->disableOriginalConstructor()->getMock();

        $entity = new \stdClass();
        $entity->$primaryKeyFieldName = 32;

        $query = new Query([ $entity ]);

        $this->entityRepository->expects($this->at(0))
            ->method('createQueryBuilder')
            ->will($this->returnValue($queryBuilder));

        $queryBuilder->expects($this->any())->method('addSelect')
            ->with('(CASE WHEN entity.id = 1 THEN 2 WHEN entity.id = 3 THEN 1 ELSE 0 END) AS HIDDEN score')
            ->will($this->returnValue($queryBuilder));
        $queryBuilder->expects($this->any())->method('where')->will($this->returnValue($queryBuilder));
        $queryBuilder->expects($this->any())->method('setParameter')->will($this->returnValue($queryBuilder));
        $queryBuilder->expects($this->any())->method('addOrderBy')->will($this->returnValue($queryBuilder));
        $queryBuilder->expects($this->any())->method('getQuery')->will($this->returnValue($query));

        $hydrator->hydrate([
            [$primaryKeyDocumentName => 1, 'title' => 'string', $documentFieldName => $configFieldValue],
            [$primaryKeyDocumentName => 3, 'title' => 'string2', $documentFieldName => $configFieldValue],
        ]);
    }

    private function createHydrator(
        $configFieldName,
        $configFieldValue,
        $configDocumentFieldName,
        $primaryKeyDocumentName = 'd_id',
        $entityConfigClass = 'class',
        $primaryKeyFieldName = 'id'
    )
    {
        /** @var EntityRepository|\PHPUnit_Framework_MockObject_MockObject $entityRepository */
        $this->entityRepository = $this->getMockBuilder('Doctrine\ORM\EntityRepository')->disableOriginalConstructor()->getMock();
        /** @var Schema|\PHPUnit_Framework_MockObject_MockObject $entityRepository $schema */
        $schema = $this->getMockBuilder('Mdiyakov\DoctrineSolrBundle\Schema\Schema')->disableOriginalConstructor()->getMock();
        $entityConfig = [
            'class' => $entityConfigClass,
            'config' => [
                [ 'name' => $configFieldName, 'value' => $configFieldValue]
            ]
        ];
        $discriminatorField = new ConfigEntityField($configFieldName, $configDocumentFieldName, true, 10);
        $primaryKeyField = new IntField($primaryKeyFieldName, $primaryKeyDocumentName, true, 10, false);

        $schema->expects($this->any())->method('getDiscriminatorConfigField')->will($this->returnValue($discriminatorField));
        $schema->expects($this->any())->method('getEntityPrimaryKeyField')->will($this->returnValue($primaryKeyField));

        return new SelectQueryHydrator(
            $this->entityRepository,
            $schema,
            $entityConfig
        );

    }
}