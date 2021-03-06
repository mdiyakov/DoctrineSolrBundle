<?php

namespace Mdiyakov\DoctrineSolrBundle\Schema\Field\Entity;

use Mdiyakov\DoctrineSolrBundle\Exception\InvalidFieldValueException;
use Mdiyakov\DoctrineSolrBundle\Schema\Field\DocumentFieldInterface;
use Symfony\Component\PropertyAccess\PropertyAccess;

abstract class Field implements DocumentFieldInterface
{
    /**
     * @var bool
     */
    private $primaryKey = false;

    /**
     * @var string
     */
    private $entityFieldName;

    /**
     * @var string
     */
    private $documentFieldName;

    /**
     * @var int
     */
    private $priority;

    /**
     * @var string
     */
    private $suggester;

    /**
     * @param string $entityFieldName
     * @param string $documentFieldName
     * @param bool $primaryKey
     * @param int $priority
     * @param string $suggester
     */
    public function __construct(
        $entityFieldName,
        $documentFieldName,
        $primaryKey,
        $priority,
        $suggester
        )
    {
        $this->entityFieldName = $entityFieldName;
        $this->documentFieldName = $documentFieldName;
        $this->primaryKey = $primaryKey;
        $this->priority = (int) $priority;
        $this->suggester = $suggester;
    }

    /**
     * @param object $entity
     * @return mixed
     */
    abstract public function getDocumentFieldValue($entity);

    /**
     * @return string
     */
    public function getEntityFieldName()
    {
        return $this->entityFieldName;
    }

    /**
     * @return string
     */
    public function getDocumentFieldName()
    {
        return $this->documentFieldName;
    }

    /**
     * @return boolean
     */
    public function isPrimaryKey()
    {
        return $this->primaryKey;
    }

    /**
     * @return int
     */
    public function getPriority()
    {
        return $this->priority;
    }

    /**
     * @param object $entity
     * @return mixed
     * @throws InvalidFieldValueException
     */
    public function getEntityFieldValue($entity)
    {
        if (!is_object($entity)) {
            throw new InvalidFieldValueException('Argument must be an object');
        }

        return PropertyAccess::createPropertyAccessor()->getValue($entity, $this->getEntityFieldName());
    }

    /**
     * @return string
     */
    public function getSuggester()
    {
        return $this->suggester;
    }
}