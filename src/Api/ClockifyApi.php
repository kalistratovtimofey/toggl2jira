<?php

namespace App\Api;

use App\Entity\TimeEntry;
use GuzzleHttp\Client;

class ClockifyApi
{
    private Client $client;

    private string $userId;

    private string $workspaceId;

    public function __construct(Client $client, string $userId, string $workspaceId)
    {
        $this->client = $client;
        $this->userId = $userId;
        $this->workspaceId = $workspaceId;
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
            $timeEntry->startTime = new \DateTime($timeEntryFromResponse['timeInterval']['start']);
            $timeEntry->finishTime = isset($timeEntryFromResponse['timeInterval']['end']) ? new \DateTime($timeEntryFromResponse['timeInterval']['end']) : null;
            $timeEntry->durationInSeconds = $timeEntry->finishTime ? ($timeEntry->finishTime->getTimestamp() - $timeEntry->startTime->getTimestamp()) : null;

            $timeEntries[] = $timeEntry;
        }

        return $timeEntries;
    }

    private function buildTimeEntriesUri(\DateTime $startDate, \DateTime $endDate = null): string
    {
        $uri = "api/v1/workspaces/{$this->workspaceId}/user/{$this->userId}/time-entries";
        $uri .= "?start=" . urlencode($this->dateToClockifyString($startDate));

        if ($endDate) {
            $uri .= "&end=" . urlencode($this->dateToClockifyString($endDate));
        }

        return $uri;
    }

    private function dateToClockifyString(\DateTime $dateTime)
    {
        return $dateTime->format("Y-m-d\TH:i:s\Z");
    }
}