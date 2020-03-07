<?php

namespace App\Service;

use App\Entity\Worklog;
use App\Service\TimeEntryStorage\TimeEntryStorage;
use App\Service\TimeEntryToWorklogConverter\TimeEntryToWorklogConverter;

class WorklogUploader
{
    /**
     * @var TimeEntryToWorklogConverter
     */
    private $timeEntryToWorklogConverter;

    /**
     * @var TimeEntryStorage
     */
    private $timeEntryStorage;
    /**
     * @var WorklogService
     */
    private $worklogService;

    public function __construct(
      TimeEntryStorage $timeEntryStorage,
      TimeEntryToWorklogConverter $timeEntryToWorklogConverter,
      WorklogService $worklogService
    )
    {
        $this->timeEntryStorage = $timeEntryStorage;
        $this->timeEntryToWorklogConverter = $timeEntryToWorklogConverter;
        $this->worklogService = $worklogService;
    }

    public function upload(string $startDate, ?string $endDate)
    {
        $workLogs = $this->timeEntryToWorklogConverter->convert(
            $this->timeEntryStorage->getTimeEntries($startDate, $endDate)
        );

        $this->uploadWorklogs($workLogs);
    }

    /**
     * @param Worklog[] $workLogs
     */
    private function uploadWorklogs(array $workLogs): void
    {
        foreach ($workLogs as $workLog) {

            if ($this->shouldUploadWorklog($workLog)) {
                $this->worklogService->addWorklogToIssue($workLog);
            }
        }
    }

    private function shouldUploadWorklog(Worklog $workLog): bool
    {
        $timeSpentInMinutes = $this->worklogService->getTimeSpentInMinutes($workLog->timeSpent);

        return $timeSpentInMinutes > 0 && !$this->worklogService->isWorklogAlreadyUploaded($workLog);
    }
}
