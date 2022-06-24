<?php

namespace App\Service\TimeEntryStorage;

class TimeEntryStorageSelector implements TimeEntryStorage
{
    private const CLOCKIFY_STORAGE = 'clockify';
    private const TOGGLE_STORAGE = 'toggl';

    public function __construct(
        private string $selectedStorage,
        private ClockifyTimeEntryStorage $clockifyTimeEntryStorage,
        private TogglTimeEntryStorage $togglTimeEntryStorage
    ) {}

    public function getTimeEntries(string $startDate, ?string $endDate): array
    {
        return $this->selectedStorage === self::CLOCKIFY_STORAGE
            ? $this->clockifyTimeEntryStorage->getTimeEntries($startDate, $endDate)
            : $this->togglTimeEntryStorage->getTimeEntries($startDate, $endDate);
    }
}