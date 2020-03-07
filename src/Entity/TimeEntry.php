<?php

namespace App\Entity;

class TimeEntry
{
    /** @var int */
    public $id;

    /** @var \DateTime */
    public $startTime;

    /** @var \DateTime */
    public $finishTime;

    /** @var int */
    public $durationInSeconds;

    /** @var string */
    public $description;
}
