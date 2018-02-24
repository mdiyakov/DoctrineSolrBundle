<?php


namespace Mdiyakov\DoctrineSolrBundle\DependencyInjection;


use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class FieldFilterCompilerPass implements CompilerPassInterface
{
    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->has('mdiyakov_doctrine_solr.filter.validator')) {
            return;
        }

        $definition = $container->findDefinition('mdiyakov_doctrine_solr.filter.validator');
        $taggedFieldFilterServices = $container->findTaggedServiceIds(
            'doctrine_solr.field_filter'
        );

        $taggedFilterServices = $container->findTaggedServiceIds(
            'doctrine_solr.service_filter'
        );


        foreach ($taggedFieldFilterServices as $id => $tags) {
            $definition->addMethodCall(
                'addFieldFilter',
                [new Reference($id)]
            );
        }

        foreach ($taggedFilterServices as $id => $tags) {
            $definition->addMethodCall(
                'addServiceEntityFilter',
                [new Reference($id)]
            );
        }
    }
}