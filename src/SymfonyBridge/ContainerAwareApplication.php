<?php

namespace SymfonyBridge;

use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use Symfony\Component\Console\Application as BaseApplication;
use Symfony\Component\DependencyInjection\ContainerInterface;

class ContainerAwareApplication extends BaseApplication implements ContainerAwareInterface
{
    use ContainerAwareTrait;
    /**
     * @return ContainerInterface
     */
    public function getContainer()
    {
        return $this->container;
    }
}
