<?php

namespace winwin\eventBus\facade;

use kuiper\di\annotation\Inject;
use kuiper\di\ContainerAwareInterface;
use kuiper\di\ContainerAwareTrait;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;

class SubscribeCommand extends Command implements ContainerAwareInterface
{
    use ContainerAwareTrait;

    /**
     * @Inject
     *
     * @var EventBusInterface
     */
    private $eventBus;

    protected function configure()
    {
        $this->setName('event-bus:subscribe')
            ->setDescription('注册订阅回调地址')
            ->addOption('uri', null, InputOption::VALUE_REQUIRED, '回调地址')
            ->addArgument('topic', InputArgument::REQUIRED, '订阅消息主题');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $topic = $input->getArgument('topic');
        $uri = $input->getOption('uri') ?: $this->getHandlerUri();
        $question = new ConfirmationQuestion(sprintf("使用以下回调订阅消息主题 %s?\n%s\n (y/n) [n] ", $topic, $uri), false);
        if (!$this->getHelper('question')->ask($input, $output, $question)) {
            return;
        }

        $this->eventBus->subscribe($topic, $uri);
    }

    /**
     * @param EventBusInterface $eventBus
     */
    public function setEventBus(EventBusInterface $eventBus)
    {
        $this->eventBus = $eventBus;
    }

    private function getHandlerUri()
    {
        return sprintf('%s/%s%s', $this->container->get('app.rpc.gateway'),
            $this->container->get('app.rpc_server.name'),
            CallbackHandler::DEFAULT_URI);
    }
}
