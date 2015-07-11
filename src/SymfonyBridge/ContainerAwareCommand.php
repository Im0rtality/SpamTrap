<?php

namespace SymfonyBridge;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\DependencyInjection\ContainerInterface;

abstract class ContainerAwareCommand extends Command
{
    /**
     * @return ContainerInterface
     *
     * @throws \LogicException
     */
    protected function getContainer()
    {
        /** @var ContainerAwareApplication $application */
        $application = $this->getApplication();

        return $application->getContainer();
    }
}
