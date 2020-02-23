<?php

namespace App\Tests\unit;

use App\Manager\TogglManager;
use App\Tests\UnitTester;
use Codeception\Test\Unit;

class TogglManagerTest extends Unit
{
    /**
     * @var UnitTester
     */
    protected $tester;

    public function testGetTimeEntries()
    {
        /** @var TogglManager $togglManager */
        $togglManager = $this->tester->grabService(TogglManager::class);
        $timeEntries = $togglManager->getTimeEntries();

        $this->assertEquals(4336691234, $timeEntries[0]->id);
        $this->assertEquals(436776436, $timeEntries[1]->id);
    }

    public function getExpectedTimeEntries()
    {

    }
}
