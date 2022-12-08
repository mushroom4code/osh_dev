<?php
class YApiPoint
{
    public $x;
    public $y;

    public function __construct($point = null, $x = null, $y = null)
    {
        if (!empty($point))
        {
            $coordinate = explode(",", $point);
            $this->x = (double)$coordinate[0];
            $this->y = (double)$coordinate[1];
        }

        if (!empty($x))
        {
            $this->x = $x;
        }

        if (!empty($y))
        {
            $this->y = $y;
        }
    }

    public function ConvertToGPSPoint($point)
    {
        $result = new YApiPoint();

        $integerX = (int)$point->x;
        $result->x = $integerX + ($point->x - $integerX) * 0.6;

        $integerY = (int)$point->y;
        $result->y = $integerY + ($point->y - $integerY) * 0.6;

        return $result;
    }

    public function ConvertGPSToYandexPoint($point)
    {
        $result = new YApiPoint();

        $integerX = (int)$point->x;
        $result->x = $integerX + ($point->x - $integerX) / 0.6;

        $integerY = (int)$point->y;
        $result->y = $integerY + ($point->y - $integerY) / 0.6;

        return $result;
    }

    public function ToString()
    {
        return "$this->x, $this->y";
    }

    public function Latitude()
    {
        return $this->y;
    }

    public function Lat()
    {
        return $this->Latitude();
    }

    public function Longitude()
    {
        return $this->x;
    }

    public function Long()
    {
        return $this->Longitude();
    }
}