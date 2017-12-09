<?php

namespace Oro\Bundle\ConfigBundle;

use Symfony\Component\DependencyInjection\Compiler\PassConfig;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

use Oro\Bundle\ConfigBundle\DependencyInjection\Compiler\ListenerExcludeConfigConnectionPass;
use Oro\Bundle\ConfigBundle\DependencyInjection\Compiler\SystemConfigurationPass;

class OroConfigBundle extends Bundle
{
    /** {@inheritdoc} */
    public function build(ContainerBuilder $container)
    {
        $container->addCompilerPass(new SystemConfigurationPass());
        $container->addCompilerPass(new ListenerExcludeConfigConnectionPass(), PassConfig::TYPE_BEFORE_OPTIMIZATION, 4);
    }
}
