<?php

namespace Mdiyakov\DoctrineSolrBundle\Filter;

interface EntityFilterInterface
{

    /**
     * @param mixed $entity
     * @return bool
     */
    public function isFilterValid($entity);

    /**
     * @return string
     */
    public function getErrorMessage();
}