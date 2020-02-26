<?php

namespace App\Service;

use App\Api\JiraApi;
use App\Api\TogglApi;
use App\DTO\WorkLogDTO;

class WorkLogService
{
    private const MINUTES_POSTFIX_IN_DURATION = "m";

    /**
     * @var TogglApi
     */
    private $togglApi;

    /**
     * @var JiraApi
     */
    private $jiraApi;

    /**
     * @var TimeEntryToWorkLogFormatter
     */
    private $timeEntryToWorkLogFormatter;

    public function __construct(TogglApi $togglApi, JiraApi $jiraApi, TimeEntryToWorkLogFormatter $timeEntryToWorkLogFormatter)
    {
        $this->togglApi = $togglApi;
        $this->jiraApi = $jiraApi;
        $this->timeEntryToWorkLogFormatter = $timeEntryToWorkLogFormatter;
    }

    public function uploadWorkLogs(string $startDate, ?string $endDate)
    {
        $startDateTime = $this->getDateTimeFromString($startDate);
        $endDateTime = $endDate ? $this->getDateTimeFromString($endDate) : null;

        $workLogs = $this->timeEntryToWorkLogFormatter->format(
            $this->togglApi->getTimeEntries($startDateTime, $endDateTime)
        );

        $this->uploadWorkLogsToJira($workLogs);
    }

    private function getDateTimeFromString(string $dateTime): \DateTime
    {
        return \DateTime::createFromFormat('Y-m-d H:i:s', $dateTime . ' 00:00:00');
    }

    /**
     * @param WorkLogDTO[] $workLogs
     */
    private function uploadWorkLogsToJira(array $workLogs): void
    {
        foreach ($workLogs as $workLog) {
            $timeSpentInMinutes = rtrim($workLog->timeSpent, self::MINUTES_POSTFIX_IN_DURATION);
            if ((int)$timeSpentInMinutes > 0) {
                $this->jiraApi->addWorkLog($workLog);
            }
        }
    }
}
