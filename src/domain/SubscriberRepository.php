<?php

namespace winwin\eventBus\domain;

use kuiper\db\AbstractCrudRepository;
use kuiper\db\annotation\Entity;
use kuiper\di\annotation\Repository;

/**
 * @Entity(Subscriber::class)
 * @Repository()
 *
 * @method Subscriber   findFirstBy($criteria)
 * @method Subscriber[] findAllBy($criteria = null) : array
 */
class SubscriberRepository extends AbstractCrudRepository
{
}
