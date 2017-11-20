<?php

namespace winwin\eventBus\models;

use winwin\db\orm\annotation\Column;
use winwin\db\orm\annotation\CreatedAt;
use winwin\db\orm\annotation\Entity;
use winwin\db\orm\annotation\Enum;
use winwin\db\orm\annotation\GeneratedValue;
use winwin\db\orm\annotation\Id;
use winwin\db\orm\annotation\Serializer;
use winwin\db\orm\annotation\Table;
use winwin\db\orm\annotation\UniqueConstraint;
use winwin\db\orm\annotation\UpdatedAt;
use winwin\eventBus\constants\EventStatus;

/**
 * Class Event.
 *
 * @Entity
 * @Table("eventbus_event", uniqueConstraints={@UniqueConstraint("event_id", columns={"event_id"})})
 */
class Event
{
    /**
     * @Id
     * @Column
     * @GeneratedValue
     *
     * @var int
     */
    private $id;

    /**
     * @Column
     * @CreatedAt
     *
     * @var \DateTime
     */
    private $createTime;

    /**
     * @Column
     * @UpdatedAt
     *
     * @var \DateTime
     */
    private $updateTime;

    /**
     * @Column
     *
     * @var string
     */
    private $eventId;

    /**
     * @Column
     *
     * @var string
     */
    private $topic;

    /**
     * @Column
     *
     * @var string
     */
    private $eventName;

    /**
     * @Serializer("json")
     * @Column
     *
     * @var array
     */
    private $payload;

    /**
     * @Enum
     * @Column
     *
     * @var EventStatus
     */
    private $status;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int $id
     *
     * @return Event
     */
    public function setId(int $id): Event
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getCreateTime()
    {
        return $this->createTime;
    }

    /**
     * @param \DateTime $createTime
     *
     * @return Event
     */
    public function setCreateTime(\DateTime $createTime): Event
    {
        $this->createTime = $createTime;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getUpdateTime()
    {
        return $this->updateTime;
    }

    /**
     * @param \DateTime $updateTime
     *
     * @return Event
     */
    public function setUpdateTime(\DateTime $updateTime): Event
    {
        $this->updateTime = $updateTime;

        return $this;
    }

    /**
     * @return string
     */
    public function getEventId(): string
    {
        return $this->eventId;
    }

    /**
     * @param string $eventId
     *
     * @return Event
     */
    public function setEventId(string $eventId): Event
    {
        $this->eventId = $eventId;

        return $this;
    }

    /**
     * @return string
     */
    public function getTopic(): string
    {
        return $this->topic;
    }

    /**
     * @param string $topic
     *
     * @return Event
     */
    public function setTopic(string $topic): Event
    {
        $this->topic = $topic;

        return $this;
    }

    /**
     * @return string
     */
    public function getEventName(): string
    {
        return $this->eventName;
    }

    /**
     * @param string $eventName
     *
     * @return Event
     */
    public function setEventName(string $eventName): Event
    {
        $this->eventName = $eventName;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getPayload()
    {
        return $this->payload;
    }

    /**
     * @param mixed $payload
     *
     * @return Event
     */
    public function setPayload($payload)
    {
        $this->payload = $payload;

        return $this;
    }

    /**
     * @return EventStatus
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param EventStatus $status
     *
     * @return Event
     */
    public function setStatus(EventStatus $status): Event
    {
        $this->status = $status;

        return $this;
    }
}
