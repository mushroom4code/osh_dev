<?
namespace Ipol\Fivepost\Bitrix\Controller;

// use \Ipol\Fivepost\Bitrix\Entity\Cache;    // TODO: Cache! (Will be changed later, \Bitrix\Main\Data\Cache used instead)
use \Ipol\Fivepost\Bitrix\Handler\Locations;
use \Ipol\Fivepost\Bitrix\Handler\LocationsDelivery;
use \Ipol\Fivepost\Bitrix\Adapter\Location;
use \Ipol\Fivepost\Core\Delivery\Location as CoreLocation;
use \Ipol\Fivepost\Core\Delivery\LocationLink;

/**
 * Class LocationLinker
 * @package namespace Ipol\Fivepost\Bitrix\Controller
 */
class LocationLinker extends AbstractController
{
    /**
     * @var string Salt for cache, also used in cache path
     */
    protected static $cacheHash = 'link_locations';

    /**
     * @var \Ipol\Fivepost\Core\Delivery\LocationLink
     */
    protected $locationLink;

    public function __construct()
    {
        parent::__construct(IPOL_FIVEPOST, IPOL_FIVEPOST_LBL);

        $this->locationLink = new LocationLink();
    }

    /**
     * Location link getter
     * @return \Ipol\Fivepost\Core\Delivery\LocationLink
     */
    public function getLocationLink()
    {
        return $this->locationLink;
    }

    /**
     * Checks if link between cms and api locations established
     * @return bool
     */
    public function ready()
    {
        return ($this->locationLink && $this->locationLink->ready());
    }

    /**
     * Try to link CMS location and corresponded API location starting from CMS side
     *
     * @param string $possiblyId Bitrix location Id or Code
     * @return \Ipol\Fivepost\Core\Delivery\LocationLink | false
     */
    public function tryLinkFromCmsSide($possiblyId)
    {
        static $cache = [];

        $cacheId = md5(self::$cacheHash.'|'.$possiblyId);

        if (isset($cache[$cacheId]))
            return $this->locationLink = $cache[$cacheId];

        $cacheTime     = 3600;
        $cachePath     = '/'.self::$MODULE_ID.'/'.self::$cacheHash;
        $cacheInstance = \Bitrix\Main\Data\Cache::createInstance();

        if ($cacheInstance->initCache($cacheTime, $cacheId, $cachePath))
        {
            $this->locationLink = $cacheInstance->GetVars();

            // ----
            // print_r('from cache ');
        }
        else
        {
            $bxLocation = new Location($possiblyId);
            if ($bxLocation->getBxId())
            {
                $this->locationLink->setCms($bxLocation->getCoreLocation());
                $code = $this->locationLink->getCms()->getCode();

                $fpLocation = LocationsDelivery::getByBitrixCode($code);
                if (!empty($fpLocation))
                {
                    $coreLocation  = new CoreLocation('api');

                    $this->locationLink->setApi(
                        $coreLocation->setName($fpLocation['NAME'])
                            ->setParent($fpLocation['PARENT_ID'])
                            ->setCode($fpLocation['LOCALITY_CODE'])
                            ->setId($fpLocation['ID'])
                            ->setCountry($fpLocation['COUNTRY'])
                            ->setRegion($fpLocation['REGION'])
                    );
                }
            }

            if (!$this->ready())
                $this->locationLink = false;

            // ----
            // print_r('from code ');

            if ($this->locationLink && $cacheInstance->startDataCache()) {
                $cacheInstance->endDataCache($this->locationLink);
            }
        }

        return $cache[$cacheId] = $this->locationLink;
    }

    /**
     * Try to link CMS location and corresponded API location starting from API side
     *
     * @param string $guid API location guid
     * @return \Ipol\Fivepost\Core\Delivery\LocationLink | false
     */
    public function tryLinkFromApiSide($guid)
    {
        static $cache = [];

        $cacheId = md5(self::$cacheHash.'|'.$guid);

        if (isset($cache[$cacheId]))
            return $this->locationLink = $cache[$cacheId];

        $cacheTime     = 3600;
        $cachePath     = '/'.self::$MODULE_ID.'/'.self::$cacheHash;
        $cacheInstance = \Bitrix\Main\Data\Cache::createInstance();

        if ($cacheInstance->initCache($cacheTime, $cacheId, $cachePath))
        {
            $this->locationLink = $cacheInstance->GetVars();

            // ----
            //print_r('ApiSide from cache ');
        }
        else
        {
            $fpLocation = LocationsDelivery::getByFiasGuid($guid);
            if (!empty($fpLocation))
            {
                $coreLocation  = new CoreLocation('api');

                $this->locationLink->setApi(
                    $coreLocation->setName($fpLocation['NAME'])
                        ->setParent($fpLocation['PARENT_ID'])
                        ->setCode($fpLocation['LOCALITY_CODE'])
                        ->setId($fpLocation['ID'])
                        ->setCountry($fpLocation['COUNTRY'])
                        ->setRegion($fpLocation['REGION'])
                );

                $bxLocation = new Location($fpLocation['BITRIX_CODE']);
                if ($bxLocation->getBxId()) {
                    $this->locationLink->setCms($bxLocation->getCoreLocation());
                }
            }

            if (!$this->ready())
                $this->locationLink = false;

            // ----
            //print_r('ApiSide from code ');

            if ($this->locationLink && $cacheInstance->startDataCache()) {
                $cacheInstance->endDataCache($this->locationLink);
            }
        }

        return $cache[$cacheId] = $this->locationLink;
    }
}