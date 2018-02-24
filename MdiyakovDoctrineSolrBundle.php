<?php

namespace Mdiyakov\DoctrineSolrBundle;

use Mdiyakov\DoctrineSolrBundle\DependencyInjection\FieldFilterCompilerPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class MdiyakovDoctrineSolrBundle extends Bundle
{

    /**
     * @param ContainerBuilder $container
     */
    public function build(ContainerBuilder $container)
    {
        parent::build($container);
        $container->addCompilerPass(new FieldFilterCompilerPass());
    }
}
