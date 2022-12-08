<?
namespace Ipol\Fivepost\Bitrix\Entity;

/**
 * Class Profiles
 * @package namespace Ipol\Fivepost\Bitrix\Entity
 */
class Profiles
{
    /**
     * Tariff ID check definitions
     */
    const TARIFF_ID_EQUALITY = 1;
    const TARIFF_ID_SUBSTRING = 2;

    protected $profiles;

    public function __construct()
    {
    }

    /**
     * @return array
     */
    protected function getProfiles()
    {
        return $this->profiles;
    }

    public function addProfile(profile $profile)
    {
        if (empty($this->getProfiles()))
            $this->profiles = array();

        $id = $profile->getId();
        $this->profiles[$id] = $profile;
    }

    /**
     * @return bool
     */
    public function inited()
    {
        return (is_array($this->getProfiles()) && count($this->getProfiles()));
    }

    /**
     * @param $id
     * @return profile
     * @throws \Exception
     */
    public function getProfile($id)
    {
        $arProfiles = $this->getProfiles();
        if ($this->inited() && array_key_exists($id, $arProfiles)) {
            return $arProfiles[$id];
        } else
            throw new \Exception('No data of profile ' . $id);
    }

    /**
     * Return array of compatible profiles
     *
     * @param string|false $desiredTariff for additional filtering
     * @return array
     */
    public function getCompability($desiredTariff = false)
    {
        if ($this->inited()) {
            $arCompability = array();

            /**
             * @var string $profileId
             * @var profile $obProfile
             */
            foreach ($this->getProfiles() as $profileId => $obProfile)
            {
                if (is_array($obProfile->getDetails()) && !empty($obProfile->getDetails())) {
                    // Apply tariff filtering
                    if (!empty($desiredTariff)) {
                        $availableTariffs = array_filter(
                            array_keys($obProfile->getDetails()),
                            function ($val) use ($desiredTariff) {
                                return (strpos($val, $desiredTariff) !== false ? true : false);
                            }
                        );

                        if (!empty($availableTariffs))
                            $arCompability[] = $profileId;
                    } else {
                        $arCompability[] = $profileId;
                    }
                }
            }
            return $arCompability;
        }
        else
        {
            return array(); // thr?
        }
    }

    public function getCalculate($profileID, $tariffId = false, $mode = self::TARIFF_ID_EQUALITY)
    {
        // thr if none
        $arProfile = $this->getProfile($profileID);

        // Get tariff with min price if tarifId not set
        if (!$tariffId) {
            $lovestPrice = false;
            $lovestId = false;
            foreach ($arProfile->getDetails() as $_tarifId => $arTarif) {
                if ($arTarif['price'] < $lovestPrice || !$lovestId) {
                    $lovestPrice = $arTarif['price'];
                    $lovestId = $_tarifId;
                }
            }
            $tariffId = $lovestId;
        } else {
            switch ($mode) {
                case self::TARIFF_ID_EQUALITY:
                    if (!array_key_exists($tariffId, $arProfile->getDetails()))
                        $tariffId = false;

                    break;

                case self::TARIFF_ID_SUBSTRING:
                    $availableTariffs = array_filter(
                        array_keys($arProfile->getDetails()),
                        function ($val) use ($tariffId) {
                            return (strpos($val, $tariffId) !== false ? true : false);
                        }
                    );
                    $tariffId = reset($availableTariffs);

                    break;
            }
        }

        if (!$tariffId) {
            throw new \Exception('Tarif not calculated ' . $tariffId);
        }

        $arTarifs = $arProfile->getDetails();

        return $this->getProfile($profileID)->setPrice($arTarifs[$tariffId]['price'])
            ->setTermMax($arTarifs[$tariffId]['termMax'])
            ->setTermMin($arTarifs[$tariffId]['termMin'])
            ->toBitrix();
    }
}