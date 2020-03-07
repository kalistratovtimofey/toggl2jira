<?php

namespace App\Service;

use App\Entity\Worklog;
use App\Event\WorklogAddedEvent;
use App\Service\TimeEntryStorage\TimeEntryStorage;
use App\Service\TimeEntryToWorklogConverter\TimeEntryToWorklogConverter;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

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

    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    public function __construct(
      TimeEntryStorage $timeEntryStorage,
      TimeEntryToWorklogConverter $timeEntryToWorklogConverter,
      WorklogService $worklogService,
      EventDispatcherInterface $eventDispatcher
    )
    {
        $this->timeEntryStorage = $timeEntryStorage;
        $this->timeEntryToWorklogConverter = $timeEntryToWorklogConverter;
        $this->worklogService = $worklogService;
        $this->eventDispatcher = $eventDispatcher;
    }

    public function upload(string $startDate, ?string $endDate)
    {
        $workLogs = $this->timeEntryToWorklogConverter->convert(
            $this->timeEntryStorage->getTimeEntries($startDate, $endDate)
        );

        $this->uploadWorklogs($workLogs);
    }

    /**
     * @param Worklog[] $worklogs
     */
    private function uploadWorklogs(array $worklogs): void
    {
        foreach ($worklogs as $worklog) {

            if ($this->shouldUploadWorklog($worklog)) {
                $this->worklogService->addWorklogToIssue($worklog);
                $this->dispatchWorklogAddedEvent($worklog);
            }
        }
    }

    private function shouldUploadWorklog(Worklog $worklog): bool
    {
        $timeSpentInMinutes = $this->worklogService->getTimeSpentInMinutes($worklog->timeSpent);

        return $timeSpentInMinutes > 0 && !$this->worklogService->isWorklogAlreadyUploaded($worklog);
    }

    private function dispatchWorklogAddedEvent(Worklog $worklog): void
    {
        $this->eventDispatcher->dispatch(
            new WorklogAddedEvent($worklog),
            WorklogAddedEvent::NAME
        );
    }
}
