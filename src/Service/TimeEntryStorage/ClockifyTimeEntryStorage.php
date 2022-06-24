<?php

namespace App\Service\TimeEntryStorage;

use App\Api\ClockifyApi;

class ClockifyTimeEntryStorage implements TimeEntryStorage
{

    public function __construct(private ClockifyApi $clockifyApi)
    {
    }

    public function getTimeEntries(string $startDate, ?string $endDate): array
    {
        return $this->clockifyApi->getTimeEntries($this->getStartOfTheDay($startDate), $this->getStartOfTheDay($endDate));
    }

    private function getStartOfTheDay(?string $dateTime): ?\DateTime
    {
        if (!$dateTime) {
            return null;
        }
        return \DateTime::createFromFormat('Y-m-d H:i:s', $dateTime . ' 00:00:00');
    }
}