<?php

namespace winwin\eventBus\domain;

use kuiper\db\annotation\CreationTimestamp;
use kuiper\db\annotation\GeneratedValue;
use kuiper\db\annotation\Id;
use kuiper\db\annotation\NaturalId;
use kuiper\db\annotation\UpdateTimestamp;
use Symfony\Component\Validator\Constraints as Assert;

class Subscriber
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
     * @Assert\Length(min=1, max=30)
     */
    private $topic;

    /**
     * @var string
     * @NaturalId()
     * @Assert\Length(min=1, max=255)
     */
    private $notifyUrl;

    /**
     * @var bool
     */
    private $enabled;

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

    /**
     * @return bool
     */
    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    /**
     * @param bool $enabled
     */
    public function setEnabled(bool $enabled): void
    {
        $this->enabled = $enabled;
    }
}
