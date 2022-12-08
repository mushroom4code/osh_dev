<?php
namespace Ipolh\SDEK\Api\Entity\Response;

use Ipolh\SDEK\Api\BadResponseException;
use Ipolh\SDEK\Api\Entity\Response\Part\Common\RequestList;
use Ipolh\SDEK\Api\Entity\Response\Part\IntakesInfo\Entity;

/**
 * Class IntakesInfo
 * @package Ipolh\SDEK\Api\Entity\Response
 */
class IntakesInfo extends AbstractResponse
{
    /**
     * @var null|Entity
     */
    protected $entity;
    /**
     * @var RequestList
     */
    protected $requests;

    /**
     * @return null|Entity
     */
    public function getEntity()
    {
        return $this->entity;
    }

    /**
     * @param array $entity
     * @return IntakesInfo
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
     * @return IntakesInfo
     * @throws BadResponseException
     */
    public function setRequests($array)
    {

        $collection = new RequestList();
        $this->requests = $collection->fillFromArray($array);
        return $this;

    }

}