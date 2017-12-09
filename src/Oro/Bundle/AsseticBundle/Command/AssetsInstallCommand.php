<?php

namespace Oro\Bundle\AsseticBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\AssetsInstallCommand as BaseAssetsInstallCommand;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

use Oro\Bundle\AsseticBundle\Command\Proxy\ContainerProxy;
use Oro\Bundle\AsseticBundle\Command\Proxy\KernelProxy;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;

/**
 * Extends Symfony 'assets:install' with '--exclude' option
 */
class AssetsInstallCommand extends BaseAssetsInstallCommand
{
    /**
     * @var ContainerProxy|null
     */
    private $containerProxy;

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        parent::configure();

        $this->setName('oro:assets:install');
        $this->addOption(
            'exclude',
            null,
            InputOption::VALUE_IS_ARRAY | InputOption::VALUE_OPTIONAL,
            'A list of bundle names which assets should be skipped'
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $excludeBundles = $input->getOption('exclude');
        /** @var ContainerProxy $containerProxy */
        $containerProxy = $this->getContainer();

        if (!empty($excludeBundles)) {
            $kernelProxy = new KernelProxy($containerProxy->get('kernel'));
            foreach ($excludeBundles as $bundleName) {
                $kernelProxy->excludeBundle($bundleName);
            }
            $containerProxy->replace('kernel', $kernelProxy);
        }


        $proxyFileSystem = new class () extends Filesystem {
            public function remove($files)
            {
                //todo: Remove it https://github.com/symfony/symfony/pull/24216
                if ($files instanceof Finder) {
                    $files->exclude(['components', 'bowerassets', 'npmassets']);
                }

                parent::remove($files);
            }
        };
        $containerProxy->replace('filesystem', $proxyFileSystem);
        $prop = new \ReflectionProperty(BaseAssetsInstallCommand::class, 'filesystem');
        $prop->setAccessible(true);
        if ($prop->getValue($this)) {
            $prop->setValue($proxyFileSystem);
        }

        $defaultWebDirectory = $this->getContainer()->getParameter('kernel.root_dir') .
            DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'web';
        $this->getDefinition()->getArgument('target')->setDefault($defaultWebDirectory);

        parent::execute($input, $output);
    }

    /**
     * {@inheritdoc}
     */
    protected function getContainer()
    {
        if (null === $this->containerProxy) {
            /** @var Application $app */
            $app = $this->getApplication();
            $this->setContainer($app->getKernel()->getContainer());
        }

        return $this->containerProxy;
    }

    /**
     * {@inheritdoc}
     */
    public function setContainer(ContainerInterface $container = null)
    {
        parent::setContainer($container);

        if (null === $container) {
            $this->containerProxy = null;
        } else {
            $this->containerProxy = new ContainerProxy($container);
        }
    }
}
