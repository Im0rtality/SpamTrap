<?php

namespace SpamTrap\ClassifierBundle\Classifier;

use MabeEnum\Enum;

/**
 * @method static MessageClass SPAM()
 * @method static MessageClass HAM()
 * @method static MessageClass UNKNOWN()
 */
class MessageClass extends Enum
{
    const SPAM = 'spam';
    const HAM = 'ham';
    const UNKNOWN = 'unknown';
}
