<?php

namespace Mdiyakov\DoctrineSolrBundle\Tests\Manager;

use Mdiyakov\DoctrineSolrBundle\Manager\IndexProcessManager;

class MyEntity {}

class IndexProcessManagerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $indexerBuilder;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $filterValidator;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $indexer;

    public function setUp()
    {
        $this->indexerBuilder = $this->getMockBuilder('Mdiyakov\DoctrineSolrBundle\Indexer\IndexerBuilder')->disableOriginalConstructor()->getMock();
        $this->filterValidator = $this->getMockBuilder('Mdiyakov\DoctrineSolrBundle\Filter\FilterValidator')->disableOriginalConstructor()->getMock();
        $this->indexer = $this->getMockBuilder('Mdiyakov\DoctrineSolrBundle\Indexer\Indexer')->disableOriginalConstructor()->getMock();
    }

    public function testReindex()
    {
        $entity = new MyEntity();
        $this->indexerBuilder->expects($this->once())
            ->method('createByEntityClass')
            ->with(get_class($entity))
            ->will($this->returnValue($this->indexer));

        $this->filterValidator->expects($this->once())
            ->method('validate')
            ->with($entity);

        $this->indexer->expects($this->once())
            ->method('indexAllFields')
            ->with($entity);

        $indexProcessManager = new IndexProcessManager($this->indexerBuilder, $this->filterValidator);
        $result = $indexProcessManager->reindex($entity);
        $this->assertEquals(true, $result->isSuccess());
    }


    public function testRemove()
    {
        $entity = new MyEntity();

        $this->indexerBuilder->expects($this->once())
            ->method('createByEntityClass')
            ->with(get_class($entity))
            ->will($this->returnValue($this->indexer));

        $this->indexer->expects($this->once())
            ->method('removeByPrimaryKey')
            ->with($entity);

        $indexProcessManager = new IndexProcessManager($this->indexerBuilder, $this->filterValidator);
        $indexProcessManager->remove($entity);
    }
}