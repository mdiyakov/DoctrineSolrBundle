<?php

namespace Mdiyakov\DoctrineSolrBundle\Schema\Field;

interface DocumentFieldInterface
{

    /**
     * @return string
     */
    public function getDocumentFieldName();

    /**
     * @return int
     */
    public function getPriority();
}