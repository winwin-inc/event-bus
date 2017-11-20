<?php

namespace winwin\eventBus\models;

use winwin\db\orm\annotation\Column;
use winwin\db\orm\annotation\CreatedAt;
use winwin\db\orm\annotation\Entity;
use winwin\db\orm\annotation\GeneratedValue;
use winwin\db\orm\annotation\Id;
use winwin\db\orm\annotation\Table;
use winwin\db\orm\annotation\UpdatedAt;

/**
 * Class Subscriber.
 *
 * @Entity
 * @Table("eventbus_subscriber")
 */
class Subscriber
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
    private $topic;

    /**
     * @Column
     *
     * @var string
     */
    private $notifyUrl;

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
     * @return Subscriber
     */
    public function setId(int $id): Subscriber
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
     * @return Subscriber
     */
    public function setCreateTime(\DateTime $createTime): Subscriber
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
     * @return Subscriber
     */
    public function setUpdateTime(\DateTime $updateTime): Subscriber
    {
        $this->updateTime = $updateTime;

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
     * @return Subscriber
     */
    public function setTopic(string $topic): Subscriber
    {
        $this->topic = $topic;

        return $this;
    }

    /**
     * @return string
     */
    public function getNotifyUrl(): string
    {
        return $this->notifyUrl;
    }

    /**
     * @param string $notifyUrl
     *
     * @return Subscriber
     */
    public function setNotifyUrl(string $notifyUrl): Subscriber
    {
        $this->notifyUrl = $notifyUrl;

        return $this;
    }
}
