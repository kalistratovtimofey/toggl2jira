<?php

namespace App\Command;

use App\Api\TogglApi;
use App\Service\WorkLogService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class WorkLogUploadCommand extends Command
{
    /**
     * @var WorkLogService
     */
    private $workLogService;
    /**
     * @var TogglApi
     */
    private $togglApi;

    public function __construct(WorkLogService $workLogService, TogglApi $togglApi)
    {
        parent::__construct();

        $this->workLogService = $workLogService;
        $this->togglApi = $togglApi;
    }

    protected static $defaultName = 'worklog:upload';

    protected function configure()
    {
        $this
          ->addOption('startDate', null,InputArgument::OPTIONAL, "Time entries start date")
          ->addOption('endDate', null, InputArgument::OPTIONAL, "Time entries end date");
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $startDate = $input->getOption('startDate');
        $endDate = $input->getOption('endDate');

        $this->workLogService->uploadWorkLogs($startDate, $endDate);
    }
}
