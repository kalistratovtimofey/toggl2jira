<?php

namespace App\Api;

use App\Entity\Worklog;
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

    public function addWorklog(Worklog $worklog): void
    {
        $uri = "/rest/api/2/issue/{$worklog->issueKey}/worklog";

        $this->client->post($uri, [
          RequestOptions::JSON => [
            'started' => $worklog->started,
            'comment' => $worklog->comment,
            'timeSpent' => $worklog->timeSpent
          ],
        ]);
    }

    public function findWorklogs(string $issueKey): array
    {
        $uri = "/rest/api/2/issue/$issueKey/worklog";

        $response = $this->client->get($uri);

        return json_decode($response->getBody(), true)['worklogs'];
    }
}
