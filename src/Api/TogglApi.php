<?php

namespace App\Api;

use App\DTO\TimeEntryDTO;
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

    public function getTimeEntries(\DateTime $startDate, \DateTime $endDate = null): array
    {
        $uri = $this->buildTimeEntriesUri($startDate, $endDate);
        $response = $this->client->get($uri);
        $timeEntries = json_decode($response->getBody(), true);

        $timeEntryDTOs = [];

        foreach ($timeEntries as $timeEntry) {
            $timeEntry = (array) $timeEntry;

            $timeEntryDTO = new TimeEntryDTO();
            $timeEntryDTO->id = $timeEntry['id'];
            $timeEntryDTO->description = $timeEntry['description'] ?? '';
            $timeEntryDTO->at = new \DateTime($timeEntry['at']);
            $timeEntryDTO->start = new \DateTime($timeEntry['start']);
            $timeEntryDTO->stop = isset($timeEntry['stop']) ? new \DateTime($timeEntry['stop']) : null;
            $timeEntryDTO->duration = $timeEntry['duration'];

            $timeEntryDTOs[] = $timeEntryDTO;
        }
        return $timeEntryDTOs;
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
