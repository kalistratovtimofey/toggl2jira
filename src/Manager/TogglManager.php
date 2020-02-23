<?php

namespace App\Manager;

use App\Api\TogglApi;
use App\DTO\TimeEntryDTO;

class TogglManager
{
    /**
     * @var TogglApi
     */
    private $togglApi;

    public function __construct(TogglApi $togglApi)
    {
        $this->togglApi = $togglApi;
    }

    /**
     * @return TimeEntryDTO[]
     */
    public function getTimeEntries(): array
    {
        $timeEntries = $this->togglApi->getTodayTimeEntries();
        $timeEntryDTOs = [];

        foreach ($timeEntries as $timeEntry) {
            $timeEntry = (array) $timeEntry;

            $timeEntryDTO = new TimeEntryDTO();
            $timeEntryDTO->id = $timeEntry['id'];
            $timeEntryDTO->description = $timeEntry['description'];
            $timeEntryDTO->at = new \DateTime($timeEntry['at']);
            $timeEntryDTO->start = new \DateTime($timeEntry['start']);
            $timeEntryDTO->stop = new \DateTime($timeEntry['stop']);
            $timeEntryDTO->duration = $timeEntry['duration'];

            $timeEntryDTOs[] = $timeEntryDTO;
        }

        return $timeEntryDTOs;
    }
}
