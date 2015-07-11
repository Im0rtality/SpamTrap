<?php

namespace SpamTrap\ClassifierBundle\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use SymfonyBridge\ContainerAwareCommand;

class ClassifyWordCommand extends ContainerAwareCommand
{
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
        $client = $this->getContainer()->get('elasticsearch');

        $query = $this->buildQuery($input->getArgument('word'));

        $response = $client->search([
            'index' => $this->getContainer()->getParameter('elastic.index.message'),
            'type'  => $this->getContainer()->getParameter('elastic.type.message'),
            'body'  => $query,
        ]);

        list($pws, $pwh, $spam) = $this->extractProbabilities($response);

        $output->writeln(sprintf(<<<MSG
Word "<info>%s</info>" was detected to be <comment>%s</comment> (spamicity: <comment>%.3f</comment>)
MSG
            , $input->getArgument('word'), $spam ? 'SPAM' : 'NOT SPAM', $pws));
    }

    /**
     * @param string $word
     * @return array
     */
    protected function buildQuery($word)
    {
        $query = [
            'query' => [
                'bool' => [
                    'must' => [
                        ['term' => ['body' => $word]],
                    ],
                ],
            ],
            'size'  => 0,
            'aggs'  => [
                'probability' => [
                    'range' => [
                        'field'  => 'spamicity',
                        'keyed'  => true,
                        'ranges' => [
                            [
                                'key' => 'ham',
                                'to'  => $this->getContainer()->getParameter('spamtrap.spamicity_threshold')
                            ],
                            [
                                'key'  => 'spam',
                                'from' => $this->getContainer()->getParameter('spamtrap.spamicity_threshold')
                            ],
                        ],
                    ],
                ],
            ],
        ];

        return $query;
    }

    /**
     * @param $response
     * @return array
     */
    private function extractProbabilities($response)
    {
        $total = $response['hits']['total'];
        $pws = $response['aggregations']['probability']['buckets']['spam']['doc_count'];
        $pwh = $response['aggregations']['probability']['buckets']['ham']['doc_count'];

        return [$pws / $total, $pwh / $total, $pws >= 1 - $this->getContainer()->getParameter('spamtrap.spamicity_threshold')];
    }
}
