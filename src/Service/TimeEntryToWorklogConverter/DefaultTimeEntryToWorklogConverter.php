<?php

namespace App\Service\TimeEntryToWorklogConverter;

use App\Entity\TimeEntry;
use App\Entity\Worklog;

class DefaultTimeEntryToWorklogConverter implements TimeEntryToWorklogConverter
{
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

            if ($issueKey === null) {
                throw new \DomainException("Issue description not found for time entry with description: {$timeEntry->description}");
            }

            $workLog->issueKey = $issueKey;

            $workLog->comment = $this->getIssueCommentFromDescription($timeEntry->description);

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
        preg_match('/.+?(?=:)/', $description, $taskKeyMatches);

        return isset($taskKeyMatches[0]) ? trim($taskKeyMatches[0]) : null;
    }

    private function getIssueCommentFromDescription(string $description): ?string
    {
        preg_match('/(?<=:)[^\]]+/', $description, $descriptionMatches);

        return isset($descriptionMatches[0]) ? trim($descriptionMatches[0]) : null;
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
