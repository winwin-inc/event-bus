<?php

namespace winwin\eventBus\domain;

use kuiper\db\annotation\CreationTimestamp;
use kuiper\db\annotation\Enumerated;
use kuiper\db\annotation\GeneratedValue;
use kuiper\db\annotation\Id;
use kuiper\db\annotation\NaturalId;
use kuiper\db\annotation\UpdateTimestamp;
use Symfony\Component\Validator\Constraints as Assert;
use winwin\eventBus\constants\EventStatus;

class Event
{
    /**
     * @Id
     * @GeneratedValue
     *
     * @var int
     */
    private $id;

    /**
     * @CreationTimestamp()
     *
     * @var \DateTime
     */
    private $createTime;

    /**
     * @UpdateTimestamp()
     *
     * @var \DateTime
     */
    private $updateTime;

    /**
     * @var string
     * @NaturalId()
     * @Assert\Length(min=1, max=36)
     */
    private $eventId;

    /**
     * @var string
     * @Assert\Length(min=1, max=30)
     */
    private $topic;

    /**
     * @var string
     * @Assert\Length(min=1, max=30)
     */
    private $eventName;

    /**
     * @var string
     * @Assert\Length(max=1000)
     */
    private $payload;

    /**
     * @Enumerated()
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
     * @return string
     */
    public function getPayload()
    {
        return $this->payload;
    }

    /**
     * @param string $payload
     *
     * @return Event
     */
    public function setPayload(string $payload)
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
