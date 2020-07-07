<?php

namespace winwin\eventBus\domain;

use kuiper\db\AbstractCrudRepository;
use kuiper\db\annotation\Entity;
use kuiper\di\annotation\Repository;

/**
 * @Entity(Log::class)
 * @Repository()
 */
class LogRepository extends AbstractCrudRepository
{
}
