<?php

namespace App\Service;

use App\Api\JiraApi;
use App\DTO\WorkLogDTO;
use App\Service\TimeEntryStorage\TimeEntryStorage;

class WorkLogService
{
    private const MINUTES_POSTFIX_IN_DURATION = "m";

    /**
     * @var JiraApi
     */
    private $jiraApi;

    /**
     * @var TimeEntryToWorklogFormatter
     */
    private $timeEntryToWorklogFormatter;

    /**
     * @var array
     */
    private $cachedWorklogs;
    /**
     * @var TimeEntryStorage
     */
    private $timeEntryStorage;

    public function __construct(TimeEntryStorage $timeEntryStorage, JiraApi $jiraApi, TimeEntryToWorklogFormatter $timeEntryToWorklogFormatter)
    {
        $this->timeEntryStorage = $timeEntryStorage;
        $this->jiraApi = $jiraApi;
        $this->timeEntryToWorklogFormatter = $timeEntryToWorklogFormatter;
    }

    public function uploadWorklogs(string $startDate, ?string $endDate)
    {
        $workLogs = $this->timeEntryToWorklogFormatter->format(
            $this->timeEntryStorage->getTimeEntries($startDate, $endDate)
        );

        $this->uploadWorklogsToJira($workLogs);
    }

    /**
     * @param WorkLogDTO[] $workLogs
     */
    private function uploadWorklogsToJira(array $workLogs): void
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
