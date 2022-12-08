<?php
namespace Ipol\Fivepost\Api\Entity\Request\Part\OrdersMake;

use Ipol\Fivepost\Api\Entity\AbstractEntity;

/**
 * Class Vendor
 * @package Ipol\Fivepost\Api\Entity\Request\Part\OrdersMake
 */
class Vendor extends AbstractEntity
{
    /**
     * @var string
     */
    protected $name;

    /**
     * @var string
     */
    protected $inn;

    /**
     * @var string|null format variants: 89XXXXXXXXX | +79ХХХХХХХХХ | 88ХХХХХХХХХ | +78ХХХХХХХХХ | 9ХХХХХХХХХ | 4ХХХХХХХХХ | 8ХХХХХХХХХ
     */
    protected $phone;

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     * @return Vendor
     */
    public function setName(string $name): Vendor
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return string
     */
    public function getInn(): string
    {
        return $this->inn;
    }

    /**
     * @param string $inn
     * @return Vendor
     */
    public function setInn(string $inn): Vendor
    {
        $this->inn = $inn;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getPhone(): ?string
    {
        return $this->phone;
    }

    /**
     * @param string|null $phone
     * @return Vendor
     */
    public function setPhone(?string $phone): Vendor
    {
        $this->phone = $phone;
        return $this;
    }
}