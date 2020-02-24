<?php

namespace App\Service;

use App\Api\JiraApi;
use App\Api\TogglApi;
use App\DTO\TimeEntryDTO;
use App\DTO\WorkLogDTO;

class WorkLogService
{
    /**
     * @var TogglApi
     */
    private $togglApi;

    /**
     * @var JiraApi
     */
    private $jiraApi;

    public function __construct(TogglApi $togglApi, JiraApi $jiraApi)
    {
        $this->togglApi = $togglApi;
        $this->jiraApi = $jiraApi;
    }

    public function uploadWorkLogs(string $startDate, ?string $endDate)
    {
        $startDateTime = $this->getDateTimeFromString($startDate);
        $endDateTime = $endDate ? $this->getDateTimeFromString($endDate) : null;

        $workLogs = $this->formatTimeEntryToWorkLog(
            $this->togglApi->getTimeEntries($startDateTime, $endDateTime)
        );

        $this->uploadWorkLogsToJira($workLogs);
    }

    private function getDateTimeFromString(string $dateTime): \DateTime
    {
        return \DateTime::createFromFormat('Y-m-d H:i:s', $dateTime . ' 00:00:00');
    }

    /**
     * @param TimeEntryDTO[] $timeEntries
     * @return WorkLogDTO[]
     */
    private function formatTimeEntryToWorkLog(array $timeEntries): array
    {
        $workLogs = [];

        foreach ($timeEntries as $timeEntry) {
            $workLog = new WorkLogDTO();

            $issueKey = $this->getIssueKeyFromTimeEntryDescription($timeEntry->description);

            if ($issueKey === null) {
                throw new \DomainException("Issue key not found for time entry with ID {$timeEntry->id}");
            }

            $workLog->issueKey = $issueKey;

            $comment = $this->getIssueCommentFromTimeEntryDescription($timeEntry->description);

            if ($issueKey === null) {
                throw new \DomainException("Issue description not found for time entry with ID {$timeEntry->id}");
            }

            $workLog->comment = $comment;
            $workLog->started = $this->getJiraCompatibleFormattedTime($timeEntry->start);
            $workLog->timeSpentSeconds = $timeEntry->duration;

            $workLogs[] = $workLog;
        }

        return $workLogs;
    }

    /**
     * @param WorkLogDTO[] $workLogs
     */
    private function uploadWorkLogsToJira(array $workLogs): void
    {
        foreach ($workLogs as $workLog) {
            if ($workLog->timeSpentSeconds > 0) {
                $this->jiraApi->addWorkLog($workLog);
            }
        }
    }

    private function getIssueKeyFromTimeEntryDescription(string $description): ?string
    {
        preg_match('/.+?(?=:)/', $description, $taskKeyMatches);

        return isset($taskKeyMatches[0]) ? trim($taskKeyMatches[0]) : null;
    }

    private function getIssueCommentFromTimeEntryDescription(string $description): ?string
    {
        preg_match('/(?<=:)[^\]]+/', $description, $descriptionMatches);

        return isset($descriptionMatches[0]) ? trim($descriptionMatches[0]) : null;
    }

    private function getJiraCompatibleFormattedTime(\DateTime $dateTime)
    {
        return $dateTime->format('Y-m-d\TH:i:s') . '.000+0000';
    }
}
