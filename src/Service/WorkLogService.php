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

    /** @var array */
    private $cachedWorklogs;

    public function __construct(TogglApi $togglApi, JiraApi $jiraApi, TimeEntryToWorkLogFormatter $timeEntryToWorkLogFormatter)
    {
        $this->togglApi = $togglApi;
        $this->jiraApi = $jiraApi;
        $this->timeEntryToWorkLogFormatter = $timeEntryToWorkLogFormatter;
    }

    public function uploadWorkLogs(string $startDate, ?string $endDate)
    {
        $startDateTime = $this->getTogglCompatibleDateTimeFromString($startDate);
        $endDateTime = $endDate ? $this->getTogglCompatibleDateTimeFromString($endDate) : null;

        $workLogs = $this->timeEntryToWorkLogFormatter->format(
            $this->togglApi->getTimeEntries($startDateTime, $endDateTime)
        );

        $this->uploadWorkLogsToJira($workLogs);
    }

    private function getTogglCompatibleDateTimeFromString(string $dateTime): \DateTime
    {
        return \DateTime::createFromFormat('Y-m-d H:i:s', $dateTime . ' 00:00:00');
    }

    /**
     * @param WorkLogDTO[] $workLogs
     */
    private function uploadWorkLogsToJira(array $workLogs): void
    {
        foreach ($workLogs as $workLog) {

            if ($this->shouldUploadWorklog($workLog)) {
                $this->jiraApi->addWorkLog($workLog);
            }
        }
    }

    private function shouldUploadWorklog(WorkLogDTO $workLogDTO): bool
    {
        $timeSpentInMinutes = (int) (rtrim($workLogDTO->timeSpent, self::MINUTES_POSTFIX_IN_DURATION));

        return $timeSpentInMinutes > 0 && !$this->isWorklogExists($workLogDTO);
    }

    private function isWorklogExists(WorkLogDTO $workLogDTO): bool
    {
        $issueWorklogs = $this->getIssueWorklogsFromLocalCache($workLogDTO->issueKey);

        $existedWorklogs = array_filter(
          $issueWorklogs,
          function ($worklog) use ($workLogDTO) {
              $issueWorklogStartedDateTime = date('Y:m:d H:i:s', strtotime($worklog['started']));
              $worklogStartedDateTime = date('Y:m:d H:i:s', strtotime($workLogDTO->started));

              return $this->isDatesEquals($issueWorklogStartedDateTime, $worklogStartedDateTime);
          }
        );

        return count($existedWorklogs) > 0;
    }

    private function getIssueWorklogsFromLocalCache(string $issueKey): array
    {
        if (!isset($this->cachedWorklogs[$issueKey])) {
            $this->cachedWorklogs[$issueKey] = $this->jiraApi->findWorkLogs($issueKey);
        }

        return $this->cachedWorklogs[$issueKey];
    }

    private function isDatesEquals(string $date1, string $date2)
    {
        $dateTime1InUnix = strtotime($date1);
        $dateTime2InUnix = strtotime($date2);

        return $dateTime1InUnix === $dateTime2InUnix;
    }
}
