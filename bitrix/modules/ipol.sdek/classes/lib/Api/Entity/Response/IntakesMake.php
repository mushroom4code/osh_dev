<?php
namespace Ipolh\SDEK\Api\Entity\Response;

use Ipolh\SDEK\Api\BadResponseException;
use Ipolh\SDEK\Api\Entity\Response\Part\Common\Entity;
use Ipolh\SDEK\Api\Entity\Response\Part\Common\RequestList;

/**
 * Class IntakesMake
 * @package Ipolh\SDEK\Api\Entity\Response
 */
class IntakesMake extends AbstractResponse
{
    /**
     * @var Entity
     */
    protected $entity;
    /**
     * @var RequestList
     */
    protected $requests;

    /**
     * @return Entity
     */
    public function getEntity()
    {
        return $this->entity;
    }

    /**
     * @param array $entity
     * @return IntakesMake
     */
    public function setEntity($entity)
    {
        $this->entity = new Entity($entity);
        return $this;
    }

    /**
     * @return RequestList
     */
    public function getRequests()
    {
        return $this->requests;
    }

    /**
     * @param array $array
     * @return IntakesMake
     * @throws BadResponseException
     */
    public function setRequests($array)
    {

        $collection = new RequestList();
        $this->requests = $collection->fillFromArray($array);
        return $this;

    }

}