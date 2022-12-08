<?php
namespace Ipol\Fivepost\Api\Entity\Response\Part\WarehousesInfo;

use Ipol\Fivepost\Api\Entity\AbstractCollection;
use Ipol\Fivepost\Api\Entity\Response\Part\Common\WarehouseEntity;

/**
 * Class WarehouseEntityList
 * @package Ipol\Fivepost\Api
 * @subpackage Response
 * @method WarehouseEntity getFirst
 * @method WarehouseEntity getNext
 * @method WarehouseEntity getLast
 */
class WarehouseEntityList extends AbstractCollection
{
    protected $WarehouseEntities;

    public function __construct()
    {
        parent::__construct('WarehouseEntities');
        $this->setChildClass(WarehouseEntity::class);
    }
}