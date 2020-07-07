<?php

namespace winwin\eventBus\domain;

use kuiper\db\AbstractCrudRepository;
use kuiper\db\annotation\Entity;
use kuiper\di\annotation\Repository;

/**
 * @Entity(Event::class)
 * @Repository()
 *
 * @method Event findFirstBy($criteria)
 */
class EventRepository extends AbstractCrudRepository
{
}
