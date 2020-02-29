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

    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    public function addWorkLog(WorkLogDTO $workLogDTO)
    {
        $uri = "/rest/api/2/issue/{$workLogDTO->issueKey}/worklog";

        $this->client->post($uri, [
          RequestOptions::JSON => [
            'started' => $workLogDTO->started,
            'comment' => $workLogDTO->comment,
            'timeSpent' => $workLogDTO->timeSpent
          ],
        ]);
    }

    public function findWorkLogs(string $issueKey)
    {
        $uri = "/rest/api/2/issue/$issueKey/worklog";

        $response = $this->client->get($uri);

        return json_decode($response->getBody(), true)['worklogs'];
    }
}
