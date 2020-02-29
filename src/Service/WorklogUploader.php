<?php

namespace App\Service;

use App\DTO\WorkLogDTO;
use App\Service\TimeEntryStorage\TimeEntryStorage;

class WorklogUploader
{
    /**
     * @var TimeEntryToWorklogFormatter
     */
    private $timeEntryToWorklogFormatter;

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
      TimeEntryToWorklogFormatter $timeEntryToWorklogFormatter,
      WorklogService $worklogService
    )
    {
        $this->timeEntryStorage = $timeEntryStorage;
        $this->timeEntryToWorklogFormatter = $timeEntryToWorklogFormatter;
        $this->worklogService = $worklogService;
    }

    public function upload(string $startDate, ?string $endDate)
    {
        $workLogs = $this->timeEntryToWorklogFormatter->format(
            $this->timeEntryStorage->getTimeEntries($startDate, $endDate)
        );

        $this->uploadWorklogs($workLogs);
    }

    /**
     * @param WorkLogDTO[] $workLogs
     */
    private function uploadWorklogs(array $workLogs): void
    {
        foreach ($workLogs as $workLog) {

            if ($this->shouldUploadWorklog($workLog)) {
                $this->worklogService->addWorklogToIssue($workLog);
            }
        }
    }

    private function shouldUploadWorklog(WorkLogDTO $workLog): bool
    {
        $timeSpentInMinutes = $this->worklogService->getTimeSpentInMinutes($workLog->timeSpent);

        return $timeSpentInMinutes > 0 && !$this->worklogService->isWorklogExists($workLog);
    }
}
