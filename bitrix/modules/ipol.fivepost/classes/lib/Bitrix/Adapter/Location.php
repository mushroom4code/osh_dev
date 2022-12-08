<?
namespace Ipol\Fivepost\Bitrix\Adapter;

use \Ipol\Fivepost\Bitrix\Handler\Locations;
use \Ipol\Fivepost\Core\Delivery\Location as CoreLocation;

/**
 * Class Location
 * @package namespace Ipol\Fivepost\Bitrix\Adapter
 */
class Location
{
    private $bxId   = false;
    private $bxCode = false;

    /**
     * @var \Ipol\Fivepost\Core\Delivery\Location
     */
    private $coreLocation = null;

    public function __construct($possiblyId)
    {
        $location = Locations::getByBitrixId($possiblyId);

        if (!empty($location))
        {
            $this->bxId = $location['ID'];
            $this->bxCode = $location['CODE'];

            $this->coreLocation = new CoreLocation('cms');
            $this->coreLocation->setId($this->bxId)
                ->setCode($this->bxCode) // Can we use $possiblyId there ?
                ->setName($location['NAME'])
                ->setCountry($location['COUNTRY'])
                ->setRegion($location['REGION'])
                ->setParent($location['PARENT_ID']);
        }
    }

    /**
     * @return \Ipol\Fivepost\Core\Delivery\Location
     */
    public function getCoreLocation()
    {
        return $this->coreLocation;
    }

    /**
     * @return mixed
     */
    public function getBxId()
    {
        return $this->bxId;
    }

    /**
     * @return mixed
     */
    public function getBxCode()
    {
        return $this->bxCode;
    }
}