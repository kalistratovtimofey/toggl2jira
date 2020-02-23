<?php

namespace App\DTO;

class TimeEntryDTO
{
    /** @var int */
    public $id;

    /** @var \DateTime */
    public $start;

    /** @var \DateTime */
    public $stop;

    /** @var int */
    public $duration;

    /** @var string */
    public $description;

    /** @var \DateTime */
    public $at;
}
