<?php

namespace App\Command;

use App\Entity\Worklog;
use App\Event\WorklogAddedEvent;
use App\Service\WorklogUploader;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class WorkLogUploadCommand extends Command
{
    /**
     * @var WorklogUploader
     */
    private $worklogUploader;
    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    public function __construct(WorklogUploader $worklogUploader, EventDispatcherInterface $eventDispatcher)
    {
        parent::__construct();

        $this->worklogUploader = $worklogUploader;
        $this->eventDispatcher = $eventDispatcher;
    }

    protected static $defaultName = 'worklog:upload';

    protected function configure()
    {
        $this
          ->addOption('from', null,InputArgument::OPTIONAL, "Time entries start date")
          ->addOption('to', null, InputArgument::OPTIONAL, "Time entries end date");
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $uploadedWorklogsCount = 0;

        $this->listenWorklogAddedEvent($output, $uploadedWorklogsCount);
        $startDate = $input->getOption('from') ?? date('Y-m-d',strtotime("-1 days"));
        $endDate = $input->getOption('to') ?? date('Y-m-d');

        $this->worklogUploader->upload($startDate, $endDate);

        $output->writeln(
          sprintf("Uploaded %s worklogs", $uploadedWorklogsCount)
        );

        return 0;
    }

    private function listenWorklogAddedEvent(OutputInterface $output, &$uploadedWorklogsCount): void
    {
        $this->eventDispatcher->addListener(
            WorklogAddedEvent::NAME,
          function (WorklogAddedEvent $worklogAddedEvent) use ($output, &$uploadedWorklogsCount) {
            $output->writeln($this->getWorklogAddedOutputText($worklogAddedEvent->worklog));
            $uploadedWorklogsCount++;
          }
        );
    }

    private function getWorklogAddedOutputText(Worklog $worklog): string
    {
        return sprintf(
          "<info>Added worklog for issue: %s, and comment: %s</info>",
          $worklog->issueKey,
          $worklog->comment
        );
    }
}
