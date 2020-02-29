<?php

namespace App\Service\TimeEntryStorage;

interface TimeEntryStorage
{
    public function getTimeEntries(string $startDate, ?string $endDate): array;
}
