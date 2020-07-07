<?php

namespace winwin\eventBus\domain;

use kuiper\db\annotation\CreationTimestamp;
use kuiper\db\annotation\GeneratedValue;
use kuiper\db\annotation\Id;
use kuiper\db\annotation\UpdateTimestamp;

class Log
{
    public const MAX_MESSAGE_LEN = 255;
    public const SUCCESS = 0;
    public const UNKNOWN_ERROR = -1;

    /**
     * @Id
     * @GeneratedValue
     *
     * @var int
     */
    private $id;

    /**
     * @UpdateTimestamp()
     *
     * @var \DateTime
     */
    private $updateTime;

    /**
     * @CreationTimestamp()
     *
     * @var \DateTime
     */
    private $createTime;

    /**
     * @var string
     */
    private $eventId;

    /**
     * @var int
     */
    private $subscriberId;

    /**
     * @var int
     */
    private $responseTime;

    /**
     * @var int
     */
    private $errorCode;

    /**
     * @var string
     */
    private $errorDesc;

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
     * @return static
     */
    public function setId($id)
    {
        $this->id = $id;

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
     * @return static
     */
    public function setUpdateTime($updateTime)
    {
        $this->updateTime = $updateTime;

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
     * @return static
     */
    public function setCreateTime($createTime)
    {
        $this->createTime = $createTime;

        return $this;
    }

    /**
     * @return string
     */
    public function getEventId()
    {
        return $this->eventId;
    }

    /**
     * @param string $eventId
     *
     * @return static
     */
    public function setEventId($eventId)
    {
        $this->eventId = $eventId;

        return $this;
    }

    /**
     * @return int
     */
    public function getSubscriberId()
    {
        return $this->subscriberId;
    }

    /**
     * @param int $subscriberId
     *
     * @return static
     */
    public function setSubscriberId($subscriberId)
    {
        $this->subscriberId = $subscriberId;

        return $this;
    }

    /**
     * @return int
     */
    public function getResponseTime()
    {
        return $this->responseTime;
    }

    /**
     * @param int $responseTime
     *
     * @return static
     */
    public function setResponseTime($responseTime)
    {
        $this->responseTime = $responseTime;

        return $this;
    }

    /**
     * @return int
     */
    public function getErrorCode()
    {
        return $this->errorCode;
    }

    /**
     * @param int $errorCode
     *
     * @return static
     */
    public function setErrorCode($errorCode)
    {
        $this->errorCode = $errorCode;

        return $this;
    }

    /**
     * @return string
     */
    public function getErrorDesc()
    {
        return $this->errorDesc;
    }

    /**
     * @param string $errorDesc
     *
     * @return static
     */
    public function setErrorDesc($errorDesc)
    {
        $this->errorDesc = $errorDesc;

        return $this;
    }

    public function setResponseTimeByStartTime(float $startTime): Log
    {
        return $this->setResponseTime((int) (1000 * (microtime(true) - $startTime)));
    }

    public function isSuccess(): bool
    {
        return Log::SUCCESS === $this->errorCode;
    }
}
