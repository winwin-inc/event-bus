<?php

namespace winwin\eventBus\commands;

use Carbon\Carbon;
use kuiper\di\annotation\Inject;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use winwin\db\orm\Repository;
use winwin\db\Statement;
use winwin\eventBus\constants\EventStatus;

class PurgeCommand extends Command implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    /**
     * 清除3天之前的事件.
     */
    const KEEP_DAYS = 3;

    /**
     * 每次删除记录数.
     */
    const BATCH_SIZE = 2000;

    /**
     * @Inject("eventBus.EventRepository")
     *
     * @var Repository
     */
    private $eventRepository;

    protected function configure()
    {
        parent::configure();
        $this->setName('event-bus:purge')
            ->setDescription('清除过期的事件,默认清除3天前的事件');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $records = 0;
        do {
            $this->eventRepository->delete(function ($stmt) {
                /* @var Statement $stmt */
                $stmt->where('status=?', EventStatus::DONE);
                $stmt->where('create_time < ?', Carbon::now()->subDays(self::KEEP_DAYS)->toDateTimeString());
                $stmt->limit(self::BATCH_SIZE);

                return $stmt;
            });
            $rows = $this->eventRepository->getLastStatement()->rowCount();
            $records += $rows;
        } while ($rows > 0);

        $message = "清除过期的事件成功，共删除 $records 条记录";
        $this->logger->info('[PurgeCommand] '.$message);
        $output->writeln("<info>$message</info>");
    }
}
