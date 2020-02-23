<?php

namespace App\Tests\_support\Mock;

use App\Api\TogglApi;

class TogglApiMock extends TogglApi
{
    public function getTimeEntriesByStartTime(\DateTime $startTime): array
    {
        return [
          [
            'id' => 436691234,
            'wid' => 777,
            'pid' => 123,
            'billable' => true,
            'start' => '2013-03-11T11:36:00+00:00',
            'stop' => '2013-03-11T15:36:00+00:00',
            'duration' => 14400,
            'description' => 'Meeting with the client',
            'tags' => [
              ''
            ],
            'at' => '2013-03-11T15:36:58+00:00',
          ],
          [
            'id' => 436776436,
            'wid' => 777,
            'billable' => false,
            'start' => '2013-03-12T10:32:43+00:00',
            'stop' => '2013-03-12T14:32:43+00:00',
            'duration' => 18400,
            'description' => 'important work',
            'tags' => [
              ''
            ],
            'at' => '2013-03-12T14:32:43+00:00',
          ],
        ];
    }
}
