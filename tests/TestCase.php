<?php

namespace winwin\eventBus;

use Dotenv\Dotenv;
use kuiper\boot\Application;
use winwin\db\ConnectionInterface;

class TestCase extends \PHPUnit\Framework\TestCase
{
    public static function setUpBeforeClass()
    {
        date_default_timezone_set('Asia/Shanghai');
        if (file_exists(__DIR__.'/.env')) {
            (new Dotenv(__DIR__))->load();
        }
    }

    protected function tearDown()
    {
        parent::tearDown();
        \Mockery::close();
    }

    protected function getConnection()
    {
        static $pdo;
        if (!$pdo) {
            $pdo = $this->getContainer()->get(ConnectionInterface::class);
        }

        return $this->createDefaultDBConnection($pdo, getenv('DB_NAME'));
    }

    public function getContainer(array $definitions = [])
    {
        $app = new Application();
        $app->useAnnotations(true)
            ->setLoader(require(__DIR__.'/../vendor/autoload.php'))
            ->loadConfig(__DIR__.'/fixtures/config');
        $app->bootstrap();

        $container = $app->getContainer();
        if ($definitions) {
            foreach ($definitions as $name => $def) {
                $container->set($name, $def);
            }
        }
        $container->get(\Symfony\Component\Console\Application::class);

        return $container;
    }
}
