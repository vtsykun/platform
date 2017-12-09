<?php

namespace Oro\Bundle\TranslationBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Reference;

class TranslatorDependencyPass implements CompilerPassInterface
{
    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        $translatorDef = $container->getDefinition('translator.default');
        $translatorDef->setClass($container->getParameter('translator.class'));
        $translatorDef->addMethodCall(
            'setDatabaseMetadataCache',
            [$container->getDefinition('oro_translation.database_translation.metadata.cache')]
        );
        $translatorDef->addMethodCall(
            'setResourceCache',
            [$container->getDefinition('oro_translation.resource.cache')]
        );

        $translatorDef->addMethodCall('setServiceContainer', [new Reference('service_container')]);
    }
}
