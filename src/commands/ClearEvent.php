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

class ClearEvent extends Command implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    /**
     * 清除3天之前的事件.
     */
    const LIFECYCLE = 3;

    /**
     * @Inject("eventBus.EventRepository")
     *
     * @var Repository
     */
    private $eventRepository;

    protected function configure()
    {
        parent::configure();
        $this->setName('clear:event')
            ->setDescription('清除过期的事件,默认清除3天前的事件');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $row = $this->eventRepository->delete(function ($stmt) {
            /* @var Statement $stmt */
            $stmt->where('status=?', 1);
            $stmt->where('create_time < ?', Carbon::parse()->subDays(self::LIFECYCLE)->toDateTimeString());

            return $stmt;
        });

        if ($row) {
            $output->writeln('<info>清除过期的事件成功</>');
        } else {
            $output->writeln('清除事件失败');
        }
    }
}
