<?php

namespace App\Command;

use App\Service\WorklogUploader;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class WorkLogUploadCommand extends Command
{
    /**
     * @var WorklogUploader
     */
    private $worklogUploader;

    public function __construct(WorklogUploader $worklogUploader)
    {
        parent::__construct();

        $this->worklogUploader = $worklogUploader;
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

        $this->worklogUploader->upload($startDate, $endDate);
    }
}
