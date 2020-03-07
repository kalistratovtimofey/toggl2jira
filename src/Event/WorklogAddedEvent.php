<?php

namespace App\Event;

use App\Entity\Worklog;

class WorklogAddedEvent
{
    public const NAME = 'worklog.added';

    public $worklog;

    public function __construct(Worklog $worklog)
    {
        $this->worklog = $worklog;
    }
}
