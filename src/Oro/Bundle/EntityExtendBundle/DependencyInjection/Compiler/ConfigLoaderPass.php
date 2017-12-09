<?php

namespace Oro\Bundle\EntityExtendBundle\DependencyInjection\Compiler;

use Oro\Bundle\EntityExtendBundle\Validator\Validation;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class ConfigLoaderPass implements CompilerPassInterface
{
    const CONFIG_LOADER_CLASS_PARAM = 'oro_entity_config.config_loader.class';

    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        $def = $container->getDefinition('validator.builder');
        $def->setFactory([Validation::class, 'createValidatorBuilder']);

        if ($container->hasParameter(self::CONFIG_LOADER_CLASS_PARAM)) {
            $container->setParameter(
                self::CONFIG_LOADER_CLASS_PARAM,
                'Oro\Bundle\EntityExtendBundle\Tools\ExtendConfigLoader'
            );
        }
    }
}
