<?php

namespace SpamTrap\ClassifierBundle\Classifier;

class Classification
{
    /** @var  MessageClass */
    private $class;
    /** @var  float */
    private $spamicity;

    /**
     * @return MessageClass
     */
    public function getClass()
    {
        return $this->class;
    }

    /**
     * @param MessageClass $class
     * @return Classification
     */
    public function setClass(MessageClass $class)
    {
        $this->class = $class;

        return $this;
    }

    /**
     * @return float
     */
    public function getSpamicity()
    {
        return $this->spamicity;
    }

    /**
     * @param float $spamicity
     * @return Classification
     */
    public function setSpamicity($spamicity)
    {
        $this->spamicity = $spamicity;

        return $this;
    }
}
