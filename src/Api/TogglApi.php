<?php

namespace App\Api;

use App\Entity\TimeEntry;
use GuzzleHttp\Client;

class TogglApi
{
    /**
     * @var Client
     */
    private $client;

    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    /**
     * @return TimeEntry[]
     * @throws \Exception
     */
    public function getTimeEntries(\DateTime $startDate, \DateTime $endDate = null): array
    {
        $uri = $this->buildTimeEntriesUri($startDate, $endDate);
        $response = $this->client->get($uri);
        $timeEntriesFromResponse = json_decode($response->getBody(), true);

        $timeEntries = [];

        foreach ($timeEntriesFromResponse as $timeEntryFromResponse) {
            $timeEntryFromResponse = (array) $timeEntryFromResponse;

            $timeEntry = new TimeEntry();
            $timeEntry->id = $timeEntryFromResponse['id'];
            $timeEntry->description = $timeEntryFromResponse['description'] ?? '';
            $timeEntry->startTime = new \DateTime($timeEntryFromResponse['start']);
            $timeEntry->finishTime = isset($timeEntryFromResponse['stop']) ? new \DateTime($timeEntryFromResponse['stop']) : null;
            $timeEntry->durationInSeconds = $timeEntryFromResponse['duration'];

            $timeEntries[] = $timeEntry;
        }

        return $timeEntries;
    }

    private function buildTimeEntriesUri(\DateTime $startDate, \DateTime $endDate = null): string
    {
        $uri = "/api/v8/time_entries";
        $uri .= "?start_date=" . urlencode($this->formDateToTogglCompatibleFormat($startDate));

        if ($endDate) {
            $uri .= "&end_date=" . urlencode($this->formDateToTogglCompatibleFormat($endDate));
        }

        return $uri;
    }

    private function formDateToTogglCompatibleFormat(\DateTime $dateTime)
    {
        return $dateTime->format(\DateTime::ATOM);
    }
}
