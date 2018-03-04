<?php

namespace Mdiyakov\DoctrineSolrBundle\Schema\Field;

class ConfigEntityField implements DocumentFieldInterface
{

    /**
     * @var int
     */
    private $priority;

    /**
     * @var string
     */
    private $configFieldName;

    /**
     * @var string
     */
    private $documentFieldName;

    /**
     * @var bool
     */
    private $discriminator;

    /**
     * @param string $configFieldName
     * @param string $documentFieldName
     * @param bool $discriminator
     * @param int $priority
     */
    public function __construct($configFieldName, $documentFieldName, $discriminator, $priority)
    {
        $this->configFieldName = $configFieldName;
        $this->documentFieldName = $documentFieldName;
        $this->discriminator = $discriminator;
        $this->priority = $priority;
    }

    /**
     * @return string
     */
    public function getConfigFieldName()
    {
        return $this->configFieldName;
    }

    /**
     * @return string
     */
    public function getDocumentFieldName()
    {
        return $this->documentFieldName;
    }

    /**
     * @param array $entityConfig
     * @return string
     */
    public function getValue($entityConfig)
    {
        $configFieldValue = '';
        foreach ($entityConfig['config'] as $config) {
            if ($config['name'] == $this->getConfigFieldName()) {
                $configFieldValue = $config['value'];
                break;
            }
        }

        return $configFieldValue;
    }

    /**
     * @return boolean
     */
    public function isDiscriminator()
    {
        return $this->discriminator;
    }

    /**
     * @return int
     */
    public function getPriority()
    {
        return $this->priority;
    }
}