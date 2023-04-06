<?php

namespace CommonPVZ;

class RussianPostDelivery extends CommonPVZ
{
    public string $delivery_name = 'RussianPost';
    public string $delivery_type = '';
    private string $russian_post_id_postfix = '_delivery_price';

    protected function __construct(string $delivery_type) {
        parent::__construct();
        $this->delivery_type = $delivery_type;
        switch ($delivery_type) {
            case 'RussianPostEms':
                $this->delivery_name = 'Почта России (EMS)';
                break;
            case 'RussianPostFirstClass':
                $this->delivery_name = 'Почта России (Посылка 1 класса)';
                break;
            case 'RussianPostRegular':
                $this->delivery_name = 'Почта России (Посылка обычная)';
                break;
        }
    }

    protected function connect()
    {
        return true;
    }

    public function getPVZ(string $city_name, array &$result_array, int &$id_feature, string $code_city)
    {
        return true;
    }

    /** Return calculate price delivery
     * @param $array
     * @return float|int|bool - false if error calculate
     */
    public function getPrice($array)
    {
        try {
            $objectId = 4020;

            $params = [
                'weight' => $array['shipment_weight'],
                'sumoc' => intval($array['shipment_cost']),
                'from' => $this->configs['fromzip'],
                'to' => $array['zip_to']
            ];

            $TariffCalculation = new \LapayGroup\RussianPost\TariffCalculation();
            $calcInfo = $TariffCalculation->calculate($objectId, $params);

            return $calcInfo->getGroundNds();
        } catch (\Throwable $e) {
            $this->errors[] = $e->getMessage();
            return array('errors' => $this->errors);
        }
    }

    public function getPriceDoorDelivery($params)
    {
        try {
            $objectId = false;
            switch ($this->delivery_type) {
                case 'RussianPostEms':
                    $objectId = 7020;
                    break;
                case 'RussianPostFirstClass':
                    $objectId = 47020;
                    break;
                case 'RussianPostRegular':
                    $objectId = 4020;
                    break;
            }

            $params = [
                'weight' => $params['shipment_weight'],
                'sumoc' => intval($params['shipment_cost']),
                'from' => $this->configs['fromzip'],
                'to' => $params['zip_to']
            ];

            $hashed_values = array($params['weight'], $params['sumoc'], $params['from'], $params['to']);
            $hash_string = md5(implode('', $hashed_values));

            $cache = \Bitrix\Main\Data\Cache::createInstance(); // получаем экземпляр класса
            if ($cache->initCache(3600, $this->delivery_type.$this->russian_post_id_postfix)) { // проверяем кеш и задаём настройки
                $cached_vars = $cache->getVars();
                if (!empty($cached_vars)) {
                    foreach ($cached_vars as $varKey => $var) {
                        if($varKey === $hash_string) {
                            return $var;
                        }
                    }
                }
            }

            if ($this->delivery_type === 'RussianPostEms') {
                $params['group'] = 0;
            }

            $TariffCalculation = new \LapayGroup\RussianPost\TariffCalculation();
            $calcInfo = $TariffCalculation->calculate($objectId, $params);
            $finalPrice = $calcInfo->getGroundNds();

            $cache->forceRewriting(true);
            if ($cache->startDataCache()) {
                $cache->endDataCache((isset($cached_vars) && !empty($cached_vars))
                    ? array_merge($cached_vars, array($hash_string => $finalPrice))
                    : array($hash_string => $finalPrice));
            }

            return $finalPrice;
        } catch (\Throwable $e) {
            $this->errors[] = $e->getMessage();
            return array('errors' => $this->errors);
        }
    }
}