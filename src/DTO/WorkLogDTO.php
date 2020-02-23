<?php

namespace App\DTO;

class WorkLogDTO
{
    /** @var string */
    public $issueKey;

    /** @var string */
    public $comment;

    /** @var \DateTime */
    public $started;

    /** @var int */
    public $timeSpentSeconds;
}
