<?php

namespace App\Service\TimeEntryToWorklogConverter;

use App\Entity\TimeEntry;
use App\Entity\Worklog;

class DefaultTimeEntryToWorklogConverter implements TimeEntryToWorklogConverter
{
    private const ISSUE_NUM_REGEX = '/(.+?(?=:))|((\w+-\d+)+)/';
    private const ISSUE_KEY_MAP = [
      'misc' => 'COM-1536',
      'meet' => 'COM-1522',
      'cr' => 'COM-1964',
      'tr' => 'COM-1632'
    ];

    public function __construct(private $defaultIssueNum)
    {
    }

    /**
     * @var bool
     */
    private $shouldIncreaseNextTimeEntry;

    /**
     * @param  TimeEntry[]  $timeEntries
     * @return Worklog[]
     */
    public function convert(array $timeEntries): array
    {
        $workLogs = [];

        foreach ($timeEntries as $key => $timeEntry) {
            $workLog = new Worklog();

            $issueKey = $this->getIssueKeyFromDescription($timeEntry->description);

            if ($issueKey === null && !$this->defaultIssueNum) {
                throw new \DomainException(
                    sprintf(
                        "Issue description not found for time entry with start time: %s and finish time: %s",
                        $timeEntry->startTime->format('Y-m-d H:i:s'),
                        $timeEntry->finishTime->format('Y-m-d H:i:s')
                    )
                );
            }

            $workLog->issueKey = $issueKey ?? $this->defaultIssueNum;

            $workLog->comment = $this->getIssueCommentFromDescription($timeEntry->description, $workLog->issueKey);

            $durationInMinutes = $timeEntry->durationInSeconds < 60 ? 1 : $timeEntry->durationInSeconds / 60;
            $this->setShouldIncreaseNextTimeEntry($durationInMinutes);

            $workLog->timeSpent = round($durationInMinutes) . Worklog::MINUTES_POSTFIX_IN_TIME_SPENT;

            $previousTimeEntryStart = isset($timeEntries[$key - 1]) ? $timeEntries[$key - 1]->startTime : null;
            $workLog->started = $this->getStartedTime($timeEntry->startTime, $previousTimeEntryStart);
            $this->shouldIncreaseNextTimeEntry = false;


            $workLogs[] = $workLog;
        }

        return $workLogs;
    }

    private function getIssueKeyFromDescription(string $description): ?string
    {
        preg_match(self::ISSUE_NUM_REGEX, $description, $taskKeyMatches);
        return isset($taskKeyMatches[0]) ?
            self::ISSUE_KEY_MAP[trim($taskKeyMatches[0])] ?? trim($taskKeyMatches[0])
            : null;
    }

    private function getIssueCommentFromDescription(string $description, string $issueKey): ?string
    {
        return str_replace($issueKey, '', $description);
    }

    private function getStartedTime(\DateTime $currentDateTime, ?\DateTime $previousDateTime)
    {
        if ($this->shouldIncreaseTimeEntry($currentDateTime, $previousDateTime)) {
            $currentDateTime->modify("+1 minute");
        }

        return $this->getJiraCompatibleFormattedTime($currentDateTime);
    }

    private function shouldIncreaseTimeEntry(\DateTime $currentDateTime, ?\DateTime $previousDateTime): bool
    {
        if (!$previousDateTime) {
            return false;
        }

        $currentDateTimeInUnix = strtotime($currentDateTime->format('Y-m-d H:i'));
        $previousDateTimeInUnix = strtotime($previousDateTime->format('Y-m-d H:i'));

        return $currentDateTimeInUnix === $previousDateTimeInUnix && $this->shouldIncreaseNextTimeEntry;
    }

    private function getJiraCompatibleFormattedTime(\DateTime $dateTime)
    {
        return $dateTime->format('Y-m-d\TH:i:s') . '.000+0000';
    }

    private function setShouldIncreaseNextTimeEntry(float $durationInMinutes): void
    {
        $this->shouldIncreaseNextTimeEntry = ($durationInMinutes -  floor($durationInMinutes)) > 0.5;
    }
}
