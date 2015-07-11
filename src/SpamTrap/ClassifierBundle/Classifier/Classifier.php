<?php

namespace SpamTrap\ClassifierBundle\Classifier;

use Elasticsearch\Client;

class Classifier
{
    /** @var  Client */
    private $client;
    /** @var  float */
    private $threshold;
    /** @var  string */
    private $index;
    /** @var  string */
    private $type;

    /**
     * @param Client $client
     * @param float  $threshold
     * @param string $index
     * @param string $type
     */
    public function __construct(Client $client, $threshold, $index, $type)
    {
        $this->client = $client;
        $this->threshold = $threshold;
        $this->index = $index;
        $this->type = $type;
    }

    public function classify($message)
    {

        $map = [];
        $pws = 1;
        $antiPws = 1;
        foreach (explode(' ', $message) as $word) {
            $classification = $this->classifyWord($word);
            $map[$word] = $classification;
            $pws *= $classification->getSpamicity();
            $antiPws *= (1 - $classification->getSpamicity());
        }

        $result = new Classification();
        $divisor = ($pws + $antiPws);

        if ($divisor < 0.00001) {
            $result->setClass(MessageClass::UNKNOWN());
        } else {
            $msgSpamicity = $pws / $divisor;

            if (1 - $this->threshold <= $msgSpamicity) {
                $result->setClass(MessageClass::SPAM());
            } else {
                $result->setClass(MessageClass::HAM());
            }
            $result->setSpamicity($msgSpamicity);
        }

        return $result;
    }

    /**
     * @param string $word
     * @return Classification
     */
    public function classifyWord($word)
    {
        list($total, $docsSpam, $docsHam) = $this->extractData($this->client->search($this->buildQuery($word)));

        $result = new Classification();

        if ($total > 0) {
            $spamicity = $docsSpam / $total;
            $result->setSpamicity($spamicity);
            if (1 - $this->threshold <= $spamicity) {
                $result->setClass(MessageClass::SPAM());
            } else {
                $result->setClass(MessageClass::HAM());
            }
        } else {
            $result->setClass(MessageClass::UNKNOWN());
        }

        return $result;
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
                                'to'  => $this->threshold
                            ],
                            [
                                'key'  => 'spam',
                                'from' => $this->threshold
                            ],
                        ],
                    ],
                ],
            ],
        ];

        return ['index' => $this->index, 'type' => $this->type, 'body' => $query];
    }

    /**
     * @param $response
     * @return int[]
     */
    private function extractData($response)
    {
        return [
            $response['hits']['total'],
            $response['aggregations']['probability']['buckets']['spam']['doc_count'],
            $response['aggregations']['probability']['buckets']['ham']['doc_count']
        ];
    }
}
