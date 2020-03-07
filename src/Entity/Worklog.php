<?php

namespace App\Entity;

class Worklog
{
    public const MINUTES_POSTFIX_IN_TIME_SPENT = "m";

    /** @var string */
    public $issueKey;

    /** @var string */
    public $comment;

    /** @var \DateTime */
    public $started;

    /** @var int */
    public $timeSpentSeconds;

    /** @var string */
    public $timeSpent;
}
