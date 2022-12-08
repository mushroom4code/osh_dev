<?php
namespace Ipolh\SDEK\Api\Entity\Response;

use Ipolh\SDEK\Api\BadResponseException;
use Ipolh\SDEK\Api\Entity\Response\Part\Common\Entity;
use Ipolh\SDEK\Api\Entity\Response\Part\Common\RequestList;

/**
 * Class IntakesDelete
 * @package Ipolh\SDEK\Api\Entity\Response
 */
class IntakesDelete extends AbstractResponse
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
     * @return Entity
     */
    public function getEntity()
    {
        return $this->entity;
    }

    /**
     * @param mixed $entity
     * @return IntakesDelete
     */
    public function setEntity($entity)
    {
        $this->entity = $entity;
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
     * @return IntakesDelete
     * @throws BadResponseException
     */
    public function setRequests($array)
    {

        $collection = new RequestList();
        $this->requests = $collection->fillFromArray($array);
        return $this;

    }

}