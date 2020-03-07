<?php

namespace App\Service;

use App\Api\JiraApi;
use App\Entity\Worklog;

class WorklogService
{
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

    public function isWorklogAlreadyUploaded(Worklog $workLog): bool
    {
        $issueWorklogs = $this->getIssueWorklogsFromLocalCache($workLog->issueKey);

        $isAlreadyUploaded = false;

        foreach ($issueWorklogs as $issueWorklog) {
            $issueWorklogStartedDate = date('Y:m:d H:i:s', strtotime($issueWorklog['started']));
            $worklogStartedDate = date('Y:m:d H:i:s', strtotime($workLog->started));

            if ($this->isDatesEquals($issueWorklogStartedDate, $worklogStartedDate)) {
                $isAlreadyUploaded = true;
                break;
            }
        }

        return $isAlreadyUploaded;
    }

    public function getTimeSpentInMinutes(string $timeSpentMinutes)
    {
        return (int) (rtrim($timeSpentMinutes, Worklog::MINUTES_POSTFIX_IN_TIME_SPENT));
    }

    public function addWorklogToIssue(Worklog $workLog)
    {
        $this->jiraApi->addWorklog($workLog);
    }

    private function getIssueWorklogsFromLocalCache(string $issueKey): array
    {
        if (!isset($this->cachedWorklogs[$issueKey])) {
            $this->cachedWorklogs[$issueKey] = $this->jiraApi->findWorklogs($issueKey);
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
