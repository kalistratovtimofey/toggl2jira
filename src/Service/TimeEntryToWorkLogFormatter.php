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

        foreach ($timeEntries as $timeEntry) {
            $workLog = new WorkLogDTO();

            $issueKey = $this->getIssueKeyFromTimeEntryDescription($timeEntry->description);

            if ($issueKey === null) {
                throw new \DomainException("Issue key not found for time entry with ID {$timeEntry->id}");
            }

            $workLog->issueKey = $issueKey;

            if ($issueKey === null) {
                throw new \DomainException("Issue description not found for time entry with ID {$timeEntry->id}");
            }

            $workLog->comment = $this->getIssueCommentFromTimeEntryDescription($timeEntry->description);;
            $workLog->started = $this->getStartedTime($timeEntry->start);
            $workLog->timeSpent = $this->getTimeSpentInMinutes($timeEntry->duration);

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

    private function getStartedTime(\DateTime $dateTime)
    {
        if ($this->shouldIncreaseNextTimeEntry) {
            $dateTime->modify("+1 minute");
            $this->shouldIncreaseNextTimeEntry = false;
        }

        return $this->getJiraCompatibleFormattedTime($dateTime);
    }

    private function getJiraCompatibleFormattedTime(\DateTime $dateTime)
    {
        return $dateTime->format('Y-m-d\TH:i:s') . '.000+0000';
    }

    private function getTimeSpentByDurationInSeconds(int $durationInSeconds)
    {
        $minutes = $durationInSeconds / 60;
        $roundedMinutes = round($minutes);

        $diff = $minutes / $roundedMinutes;

        $this->shouldIncreaseNextTimeEntry = $diff > 0.5;

        return $roundedMinutes;
    }
}
