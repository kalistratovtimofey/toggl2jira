<?php

namespace App\Service\TimeEntryStorage;

use App\Api\TogglApi;

class TogglTimeEntryStorage implements TimeEntryStorage
{
    /**
     * @var TogglApi
     */
    private $togglApi;

    public function __construct(TogglApi $togglApi)
    {
        $this->togglApi = $togglApi;
    }

    public function getTimeEntries(string $startDate, ?string $endDate): array
    {
        $startDateTime = $this->getTogglCompatibleDateTimeFromString($startDate);
        $endDateTime = $endDate ? $this->getTogglCompatibleDateTimeFromString($endDate) : null;

        return $this->togglApi->getTimeEntries($startDateTime, $endDateTime);
    }

    private function getTogglCompatibleDateTimeFromString(string $dateTime): \DateTime
    {
        return \DateTime::createFromFormat('Y-m-d H:i:s', $dateTime . ' 00:00:00');
    }
}
