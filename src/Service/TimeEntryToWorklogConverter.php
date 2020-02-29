<?php

namespace App\Service;

use App\DTO\TimeEntryDTO;
use App\DTO\WorkLogDTO;

class TimeEntryToWorklogConverter
{
    /**
     * @var bool
     */
    private $shouldIncreaseNextTimeEntry;

    /**
     * @param  TimeEntryDTO[]  $timeEntries
     * @return WorkLogDTO[]
     */
    public function convert(array $timeEntries)
    {
        $workLogs = [];

        foreach ($timeEntries as $key => $timeEntry) {
            $workLog = new WorkLogDTO();

            $issueKey = $this->getIssueKeyFromDescription($timeEntry->description);

            if ($issueKey === null) {
                throw new \DomainException("Issue key not found for time entry with ID {$timeEntry->id}");
            }

            $workLog->issueKey = $issueKey;

            if ($issueKey === null) {
                throw new \DomainException("Issue description not found for time entry with ID {$timeEntry->id}");
            }

            $workLog->comment = $this->getIssueCommentFromDescription($timeEntry->description);

            $durationInMinutes = $timeEntry->duration < 60 ? 1 : $timeEntry->duration / 60;
            $this->setShouldIncreaseNextTimeEntry($durationInMinutes);

            $workLog->timeSpent = round($durationInMinutes) . WorklogService::MINUTES_POSTFIX_IN_DURATION;

            $previousTimeEntryStart = isset($timeEntries[$key - 1]) ? $timeEntries[$key - 1]->start : null;
            $workLog->started = $this->getStartedTime($timeEntry->start, $previousTimeEntryStart);
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
