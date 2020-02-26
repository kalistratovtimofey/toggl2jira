<?php

namespace App\Service;

use App\DTO\TimeEntryDTO;
use App\DTO\WorkLogDTO;

class TimeEntryToWorkLogFormatter
{
    /**
     * @var bool
     */
    private $shouldIncreaseNextTimeEntry;

    /**
     * @param  TimeEntryDTO[]  $timeEntries
     * @return WorkLogDTO[]
     */
    public function format(array $timeEntries)
    {
        $workLogs = [];

        foreach ($timeEntries as $key => $timeEntry) {
            $workLog = new WorkLogDTO();

            $issueKey = $this->getIssueKeyFromTimeEntryDescription($timeEntry->description);

            if ($issueKey === null) {
                throw new \DomainException("Issue key not found for time entry with ID {$timeEntry->id}");
            }

            $workLog->issueKey = $issueKey;

            if ($issueKey === null) {
                throw new \DomainException("Issue description not found for time entry with ID {$timeEntry->id}");
            }

            $workLog->comment = $this->getIssueCommentFromTimeEntryDescription($timeEntry->description);

            $workLog->timeSpent = $this->getTimeSpentInMinutes($timeEntry->duration);

            $previousTimeEntryStart = isset($timeEntries[$key - 1]) ? $timeEntries[$key - 1]->start : null;
            $workLog->started = $this->getStartedTime($timeEntry->start, $previousTimeEntryStart);
            $this->shouldIncreaseNextTimeEntry = false;


            $workLogs[] = $workLog;
        }

        return $workLogs;
    }

    private function getTimeSpentInMinutes(string $duration): string
    {
        $timeEntryInMinutes = $this->getTimeSpentByDurationInSeconds($duration);

        return"{$timeEntryInMinutes}m";
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

    private function getTimeSpentByDurationInSeconds(int $durationInSeconds)
    {
        $minutes = $durationInSeconds < 60 ? 1 : $durationInSeconds / 60;
        $roundedMinutes = round($minutes);

        $diff = $minutes - $roundedMinutes;

        // TODO extract coefficient to parameter
        $this->shouldIncreaseNextTimeEntry = $diff > 0.6;

        return $roundedMinutes;
    }
}
