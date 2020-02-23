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

    public function getTodayTimeEntries(): array
    {
        $uri = "/api/v8/time_entries";
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
            $timeEntryDTO->stop = new \DateTime($timeEntry['stop']);
            $timeEntryDTO->duration = $timeEntry['duration'];

            $timeEntryDTOs[] = $timeEntryDTO;
        }
        return $timeEntryDTOs;
    }
}
