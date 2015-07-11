<?php

namespace SpamTrap\ClassifierBundle\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use SymfonyBridge\ContainerAwareCommand;

class ClassifyWordCommand extends ContainerAwareCommand
{
    const MESSAGE = <<<MSG
Word "<info>%s</info>" was detected to be <comment>%s</comment> (spamicity: <comment>%.3f</comment>)
MSG;

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('spamtrap:classify:word')
            ->addArgument('word', InputArgument::REQUIRED);
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $classifier = $this->getContainer()->get('spamtrap_classifier.classifier');
        $result = $classifier->classifyWord($input->getArgument('word'));

        $output->writeln(
            sprintf(
                self::MESSAGE,
                $input->getArgument('word'),
                $result->getClass()->getName(),
                $result->getSpamicity()
            )
        );
    }
}
