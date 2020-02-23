<?php

namespace App\Api;

use App\DTO\WorkLogDTO;
use GuzzleHttp\Client;
use GuzzleHttp\RequestOptions;

class JiraApi
{
    /**
     * @var Client
     */
    private $client;
    /**
     * @var string
     */
    private $diffWithUtcInHours;

    public function __construct(Client $client, string $diffWithUtcInHours)
    {
        $this->client = $client;
        $this->diffWithUtcInHours = $diffWithUtcInHours;
    }

    public function addWorkLog(WorkLogDTO $workLogDTO)
    {
        $uri = "/rest/api/2/issue/{$workLogDTO->issueKey}/worklog";

        $this->client->post($uri, [
          RequestOptions::JSON => [
            'started' => $this->getFormattedTime($workLogDTO->started),
            'comment' => $workLogDTO->comment,
            'timeSpentSeconds' => $workLogDTO->timeSpentSeconds
          ],
        ]);
    }

    private function getFormattedTime(\DateTime $dateTime)
    {
        return $dateTime
            ->modify("{$this->diffWithUtcInHours} hours")
            ->format('Y-m-d\TH:m:s') . '.000+0000';
    }
}
