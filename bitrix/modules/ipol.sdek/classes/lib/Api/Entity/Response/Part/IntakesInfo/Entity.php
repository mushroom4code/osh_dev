<?php
namespace Ipolh\SDEK\Api\Entity\Response\Part\IntakesInfo;

use DateTime;
use Ipolh\SDEK\Api\Entity\Response\Part\Common\CdekLocation;
use Ipolh\SDEK\Api\Entity\Response\Part\Common\StatusList;

/**
 * Class Entity
 * @package Ipolh\SDEK\Api\Entity\Response\Part\IntakeInfo
 */
class Entity extends \Ipolh\SDEK\Api\Entity\Response\Part\Common\Entity
{
    /**
     * @var integer (long)
     */
    protected $intake_number;
    /**
     * @var integer (long)
     */
    protected $cdek_number;
    /**
     * @var string (UUID)
     */
    protected $order_uuid;
    /**
     * @var DateTime (yyyy-MM-dd)
     */
    protected $intake_date;
    /**
     * @var DateTime (time)
     */
    protected $intake_time_from;
    /**
     * @var DateTime (time)
     */
    protected $intake_time_to;
    /**
     * @var DateTime (time)
     */
    protected $lunch_time_from;
    /**
     * @var DateTime (time)
     */
    protected $lunch_time_to;
    /**
     * @var string
     */
    protected $name;
    /**
     * @var integer
     */
    protected $weight;
    /**
     * @var integer
     */
    protected $length;
    /**
     * @var integer
     */
    protected $width;
    /**
     * @var integer
     */
    protected $height;
    /**
     * @var string
     */
    protected $comment;
    /**
     * @var Sender
     */
    protected $sender;
    /**
     * @var CdekLocation
     */
    protected $from_location;
    /**
     * @var bool
     */
    protected $need_call;
    /**
     * @var StatusList
     */
    protected $statuses;

    /**
     * @return int
     */
    public function getIntakeNumber()
    {
        return $this->intake_number;
    }

    /**
     * @param int $intake_number
     * @return Entity
     */
    public function setIntakeNumber($intake_number)
    {
        $this->intake_number = $intake_number;
        return $this;
    }

    /**
     * @return int
     */
    public function getCdekNumber()
    {
        return $this->cdek_number;
    }

    /**
     * @param int $cdek_number
     * @return Entity
     */
    public function setCdekNumber($cdek_number)
    {
        $this->cdek_number = $cdek_number;
        return $this;
    }

    /**
     * @return string
     */
    public function getOrderUuid()
    {
        return $this->order_uuid;
    }

    /**
     * @param string $order_uuid
     * @return Entity
     */
    public function setOrderUuid($order_uuid)
    {
        $this->order_uuid = $order_uuid;
        return $this;
    }

    /**
     * @return DateTime
     */
    public function getIntakeDate()
    {
        return $this->intake_date;
    }

    /**
     * @param DateTime $intake_date
     * @return Entity
     */
    public function setIntakeDate($intake_date)
    {
        $this->intake_date = $intake_date;
        return $this;
    }

    /**
     * @return DateTime
     */
    public function getIntakeTimeFrom()
    {
        return $this->intake_time_from;
    }

    /**
     * @param DateTime $intake_time_from
     * @return Entity
     */
    public function setIntakeTimeFrom($intake_time_from)
    {
        $this->intake_time_from = $intake_time_from;
        return $this;
    }

    /**
     * @return DateTime
     */
    public function getIntakeTimeTo()
    {
        return $this->intake_time_to;
    }

    /**
     * @param DateTime $intake_time_to
     * @return Entity
     */
    public function setIntakeTimeTo($intake_time_to)
    {
        $this->intake_time_to = $intake_time_to;
        return $this;
    }

    /**
     * @return DateTime
     */
    public function getLunchTimeFrom()
    {
        return $this->lunch_time_from;
    }

    /**
     * @param DateTime $lunch_time_from
     * @return Entity
     */
    public function setLunchTimeFrom($lunch_time_from)
    {
        $this->lunch_time_from = $lunch_time_from;
        return $this;
    }

    /**
     * @return DateTime
     */
    public function getLunchTimeTo()
    {
        return $this->lunch_time_to;
    }

    /**
     * @param DateTime $lunch_time_to
     * @return Entity
     */
    public function setLunchTimeTo($lunch_time_to)
    {
        $this->lunch_time_to = $lunch_time_to;
        return $this;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     * @return Entity
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return int
     */
    public function getWeight()
    {
        return $this->weight;
    }

    /**
     * @param int $weight
     * @return Entity
     */
    public function setWeight($weight)
    {
        $this->weight = $weight;
        return $this;
    }

    /**
     * @return int
     */
    public function getLength()
    {
        return $this->length;
    }

    /**
     * @param int $length
     * @return Entity
     */
    public function setLength($length)
    {
        $this->length = $length;
        return $this;
    }

    /**
     * @return int
     */
    public function getWidth()
    {
        return $this->width;
    }

    /**
     * @param int $width
     * @return Entity
     */
    public function setWidth($width)
    {
        $this->width = $width;
        return $this;
    }

    /**
     * @return int
     */
    public function getHeight()
    {
        return $this->height;
    }

    /**
     * @param int $height
     * @return Entity
     */
    public function setHeight($height)
    {
        $this->height = $height;
        return $this;
    }

    /**
     * @return string
     */
    public function getComment()
    {
        return $this->comment;
    }

    /**
     * @param string $comment
     * @return Entity
     */
    public function setComment($comment)
    {
        $this->comment = $comment;
        return $this;
    }

    /**
     * @return Sender
     */
    public function getSender()
    {
        return $this->sender;
    }

    /**
     * @param Sender $sender
     * @return Entity
     */
    public function setSender($sender)
    {
        $this->sender = $sender;
        return $this;
    }

    /**
     * @return CdekLocation
     */
    public function getFromLocation()
    {
        return $this->from_location;
    }

    /**
     * @param CdekLocation $from_location
     * @return Entity
     */
    public function setFromLocation($from_location)
    {
        $this->from_location = $from_location;
        return $this;
    }

    /**
     * @return bool
     */
    public function isNeedCall()
    {
        return $this->need_call;
    }

    /**
     * @param bool $need_call
     * @return Entity
     */
    public function setNeedCall($need_call)
    {
        $this->need_call = $need_call;
        return $this;
    }

    /**
     * @return StatusList
     */
    public function getStatuses()
    {
        return $this->statuses;
    }

    /**
     * @param StatusList $statuses
     * @return Entity
     */
    public function setStatuses($statuses)
    {
        $this->statuses = $statuses;
        return $this;
    }

}