<?php

namespace App\Service;

use App\Api\JiraApi;
use App\DTO\WorkLogDTO;

class WorklogService
{
    public const MINUTES_POSTFIX_IN_DURATION = "m";

    /**
     * @var array
     */
    private $cachedWorklogs;
    /**
     * @var JiraApi
     */
    private $jiraApi;

    public function __construct(JiraApi $jiraApi)
    {
        $this->jiraApi = $jiraApi;
    }

    public function isWorklogExists(WorkLogDTO $workLog): bool
    {
        $issueWorklogs = $this->getIssueWorklogsFromLocalCache($workLog->issueKey);

        $existedWorklogs = array_filter(
          $issueWorklogs,
          function ($issueWorklog) use ($workLog) {
              $issueWorklogStartedDateTime = date('Y:m:d H:i:s', strtotime($issueWorklog['started']));
              $worklogStartedDateTime = date('Y:m:d H:i:s', strtotime($workLog->started));

              return $this->isDatesEquals($issueWorklogStartedDateTime, $worklogStartedDateTime);
          }
        );

        return count($existedWorklogs) > 0;
    }

    public function getTimeSpentInMinutes(string $timeSpentMinutes)
    {
        return (int) (rtrim($timeSpentMinutes, self::MINUTES_POSTFIX_IN_DURATION));
    }

    public function addWorklogToIssue(WorkLogDTO $workLog)
    {
        $this->jiraApi->addWorkLog($workLog);
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
