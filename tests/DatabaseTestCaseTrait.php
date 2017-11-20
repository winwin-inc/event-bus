<?php

namespace winwin\eventBus;

use PHPUnit\DbUnit\TestCaseTrait;

trait DatabaseTestCaseTrait
{
    use TestCaseTrait {
        tearDown as dbTearDown;
    }

    protected function tearDown()
    {
        parent::tearDown();
        $this->dbTearDown();
    }
}
