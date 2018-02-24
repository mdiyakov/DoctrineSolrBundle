<?php

namespace Mdiyakov\DoctrineSolrBundle\Schema\Field;

class ConfigEntityField
{
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
     */
    public function __construct($configFieldName, $documentFieldName, $discriminator)
    {
        $this->configFieldName = $configFieldName;
        $this->documentFieldName = $documentFieldName;
        $this->discriminator = $discriminator;
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
}