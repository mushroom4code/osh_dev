<?php
namespace Ipol\Fivepost\Api\Entity\Response;

use Ipol\Fivepost\Api\Entity\Response\Part\WarehousesInfo\WarehouseEntityList;

/**
 * Class WarehousesInfo
 * @package Ipol\Fivepost\Api\Entity\Response
 */
class WarehousesInfo extends AbstractResponse
{
    /**
     * @var WarehouseEntityList|null
     */
    protected $content;

    /**
     * @var int
     */
    protected $totalPages;

    /**
     * @var int
     */
    protected $numberOfElements;

    /**
     * @var int
     */
    protected $size;

    /**
     * @var int page number
     */
    protected $number;

    /**
     * @return WarehouseEntityList|null
     */
    public function getContent(): ?WarehouseEntityList
    {
        return $this->content;
    }

    /**
     * @param array $array
     * @return WarehousesInfo
     */
    public function setContent(array $array): WarehousesInfo
    {
        $collection = new WarehouseEntityList();
        $this->content = $collection->fillFromArray($array);
        return $this;
    }

    /**
     * @return int
     */
    public function getTotalPages(): int
    {
        return $this->totalPages;
    }

    /**
     * @param int $totalPages
     * @return WarehousesInfo
     */
    public function setTotalPages(int $totalPages): WarehousesInfo
    {
        $this->totalPages = $totalPages;
        return $this;
    }

    /**
     * @return int
     */
    public function getNumberOfElements(): int
    {
        return $this->numberOfElements;
    }

    /**
     * @param int $numberOfElements
     * @return WarehousesInfo
     */
    public function setNumberOfElements(int $numberOfElements): WarehousesInfo
    {
        $this->numberOfElements = $numberOfElements;
        return $this;
    }

    /**
     * @return int
     */
    public function getSize(): int
    {
        return $this->size;
    }

    /**
     * @param int $size
     * @return WarehousesInfo
     */
    public function setSize(int $size): WarehousesInfo
    {
        $this->size = $size;
        return $this;
    }

    /**
     * @return int
     */
    public function getNumber(): int
    {
        return $this->number;
    }

    /**
     * @param int $number
     * @return WarehousesInfo
     */
    public function setNumber(int $number): WarehousesInfo
    {
        $this->number = $number;
        return $this;
    }
}