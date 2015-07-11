<?php

namespace SpamTrap\ClassifierBundle\Command;

use SpamTrap\ClassifierBundle\Classifier\Classification;
use SpamTrap\ClassifierBundle\Classifier\MessageClass;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Exception\FileNotFoundException;
use SymfonyBridge\ContainerAwareCommand;

class ClassifyTestCommand extends ContainerAwareCommand
{
    const MESSAGE_PASS = "PASSED\t%s\t%s\t%.3f\t%s";
    const MESSAGE_FAIL = "FAILED\t%s\t%s\t%.3f\t%s";

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('spamtrap:classify:test')
            ->addArgument('file', InputArgument::REQUIRED);
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $classifier = $this->getContainer()->get('spamtrap_classifier.classifier');
        $file = $input->getArgument('file');
        if (!file_exists($file)) {
            throw new FileNotFoundException($file);
        }

        $messages = json_decode(file_get_contents($file), true);

        $fp = 0;
        $fn = 0;
        $tp = 0;
        $tn = 0;
        $unknown = 0;

        $progress = new ProgressBar($output, count($messages));
        foreach ($messages as $message) {
            $result = $classifier->classify($message['body']);

            $correct = $this->isCorrect($result, $message);
            $tmpl = $correct ? self::MESSAGE_PASS : self::MESSAGE_FAIL;

            switch ($result->getClass()->getValue()) {
                case (MessageClass::SPAM):
                    if ($correct) {
                        $tp++;
                    } else {
                        $fp++;
                    }
                    break;
                case (MessageClass::HAM):
                    if ($correct) {
                        $tn++;
                    } else {
                        $fn++;
                    }
                    break;
                default:
                    $unknown++;
            }

            if ($output->getVerbosity() >= OutputInterface::VERBOSITY_VERY_VERBOSE) {
                $output->writeln(
                    sprintf(
                        $tmpl,
                        $result->getClass()->getName(),
                        $message['spam'] ? 'SPAM' : 'HAM',
                        $result->getSpamicity(),
                        $this->prepareText($message['body'], 80)
                    )
                );
            }

            $progress->advance();
        }

        $progress->finish();

        $output->writeln('');

        $output->writeln(sprintf('<info>Accuracy %.5f%%</info>', 100 * ($tp + $tn) / count($messages)));
        $output->writeln('Statistics:');
        $output->writeln(sprintf("  Total:\t\t%d", count($messages)));
        $output->writeln(sprintf("  Passed:\t\t%d", $tp + $tn));
        $output->writeln(sprintf("  True positive:\t%d", $tp));
        $output->writeln(sprintf("  True negative:\t%d", $tn));
        $output->writeln(sprintf("  False positive:\t%d", $fp));
        $output->writeln(sprintf("  False negative:\t%d", $fn));
    }

    /**
     * @param $result
     * @param $message
     * @return bool
     */
    private function isCorrect(Classification $result, $message)
    {
        return $result->getClass()->is(MessageClass::SPAM) && $message['spam']
        || $result->getClass()->is(MessageClass::HAM) && !$message['spam'];
    }

    private function prepareText($text, $limit)
    {
        $text = str_replace(["\r\n", "\r", "\n"], '', $text);

        if (strlen($text) > $limit) {
            $text = substr($text, 0, $limit - 5) . '[...]';
        } else {
            $text .= str_repeat(' ', $limit - strlen($text));
        }

        return $text;
    }
}
