<?php

namespace App\Api;

use GuzzleHttp\Client;
use MorningTrain\TogglApi\TogglApi as ExternalTogglApi;

class TogglApi
{
    /**
     * @var Client
     */
    private $client;

    private $togglClient;

    public function __construct(Client $client, string $apiKey)
    {
        $this->client = $client;
        $this->togglClient = new ExternalTogglApi($apiKey);
    }

    public function getTodayTimeEntries(): array
    {
        return $this->togglClient->getTimeEntriesInRange('2020-02-01T00:00:00.000Z', '2020-02-15T00:00:00.000Z');
    }
}
