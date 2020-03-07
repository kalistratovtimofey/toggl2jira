<?php

namespace App\Service\TimeEntryToWorklogConverter;

interface TimeEntryToWorklogConverter
{
    public function convert(array $timeEntries): array;
}
