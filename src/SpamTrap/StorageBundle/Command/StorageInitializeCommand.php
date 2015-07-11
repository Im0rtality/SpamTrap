<?php

namespace SpamTrap\StorageBundle\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use SymfonyBridge\ContainerAwareCommand;

class StorageInitializeCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('spamtrap:storage:initialize');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $client = $this->getContainer()->get('elasticsearch');

        $indexConfig = $this->getContainer()->getParameter('elastic.mapping');
        $client->indices()->create($indexConfig);
    }
}
