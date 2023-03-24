<?php

namespace CommonPVZ;

class RussianPostDelivery extends CommonPVZ
{
    public string $delivery_name = 'RussianPost';

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
            $objectId = 4020;

            $params = [
                'weight' => $params['shipment_weight'],
                'sumoc' => intval($params['shipment_cost']),
                'from' => $this->configs['fromzip'],
                'to' => $params['zip_to']
            ];

            $TariffCalculation = new \LapayGroup\RussianPost\TariffCalculation();
            $calcInfo = $TariffCalculation->calculate($objectId, $params);

            return $calcInfo->getGroundNds();
        } catch (\Throwable $e) {
            $this->errors[] = $e->getMessage();
            return array('errors' => $this->errors);
        }
    }
}