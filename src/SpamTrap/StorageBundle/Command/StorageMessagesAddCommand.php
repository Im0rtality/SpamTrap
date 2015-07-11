<?php

namespace SpamTrap\StorageBundle\Command;

use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Exception\FileNotFoundException;
use SymfonyBridge\ContainerAwareCommand;

class StorageMessagesAddCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('spamtrap:storage:messages:add')
            ->addArgument('file', InputArgument::OPTIONAL, 'JSON file with messages', 'app/data/messages.json');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $client = $this->getContainer()->get('elasticsearch');

        $file = $input->getArgument('file');
        if (!file_exists($file)) {
            throw new FileNotFoundException($file);
        }

        $messages = json_decode(file_get_contents($file), true);

        $progress = new ProgressBar($output, count($messages));
        foreach ($messages as $message) {
            $doc = [
                'index' => $this->getContainer()->getParameter('elastic.index.message'),
                'type'  => $this->getContainer()->getParameter('elastic.type.message'),
                'body'  => [
                    'body'      => $message['body'],
                    'spamicity' => $message['spam'] ? 1 : 0,
                ]
            ];
            $client->index($doc);
            $progress->advance();
        }
        $progress->finish();

        $output->writeln('');
    }
}
