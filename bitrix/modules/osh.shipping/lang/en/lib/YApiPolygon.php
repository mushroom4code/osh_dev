<?php
class YApiPolygon
{
    public $points;

    public function __construct($points = null)
    {
        $this->points = $points;

        if (empty($this->points))
        {
            $this->points = array();
        }
    }

    public function IsInPolygon($MainPoint)
    {
        $result = false;

        for ($i = 0, $j = count($this->points) - 1; $i < count($this->points); $j = $i++)
        {
            if ($this->points[$i]->y < $MainPoint->y && $this->points[$j]->y >= $MainPoint->y || $this->points[$j]->y < $MainPoint->y && $this->points[$i]->y >= $MainPoint->y)
            {
                if ($this->points[$i]->x + ($MainPoint->y - $this->points[$i]->y ) / ($this->points[$j]->y - $this->points[$i]->y ) * ($this->points[$j]->x - $this->points[$i]->x ) < $MainPoint->x)
                {
                    $result = !$result;
                }
            }
        }

        return $result ? true : false;
    }

    public function GetClosestPoint($point)
    {
        $api = new YApi();
        $result = array();
        $sort = array();

        foreach ($this->points as $p)
        {
            $d = $api->GetDistance($p, $point);
            $result[$d] = $p;
            $sort[] = $d;
        }

        $key = min($sort);

        return $result[$key];
    }

    public static function GetPointsFromString($StringPoints)
    {
        $coordinates = explode(" ", $StringPoints);
        $result = array_fill(0, count($coordinates) / 2, null);

        $j = 0;

        for ($i = 0; $i < count(coordinates);  $i = $i + 2)
        {
            $point = new YApiPoint();
            $point->x = (double)$coordinates[$i];
            $point->y = (double)$coordinates[$i + 1];

            $result[$j] = $point;

            if ($j >= count($result))
            {
                break;
            }

            $j++;
        }

        return result;
    }

    public static function GetMkadPolygon()
    {
        $mkad_km = array(
            array(1, 37.842762, 55.774558),
            array(2, 37.842789, 55.76522),
            array(3, 37.842627, 55.755723),
            array(4, 37.841828, 55.747399),
            array(5, 37.841217, 55.739103),
            array(6, 37.840175, 55.730482),
            array(7, 37.83916, 55.721939),
            array(8, 37.837121, 55.712203),
            array(9, 37.83262, 55.703048),
            array(10, 37.829512, 55.694287),
            array(11, 37.831353, 55.68529),
            array(12, 37.834605, 55.675945),
            array(13, 37.837597, 55.667752),
            array(14, 37.839348, 55.658667),
            array(15, 37.833842, 55.650053),
            array(16, 37.824787, 55.643713),
            array(17, 37.814564, 55.637347),
            array(18, 37.802473, 55.62913),
            array(19, 37.794235, 55.623758),
            array(20, 37.781928, 55.617713),
            array(21, 37.771139, 55.611755),
            array(22, 37.758725, 55.604956),
            array(23, 37.747945, 55.599677),
            array(24, 37.734785, 55.594143),
            array(25, 37.723062, 55.589234),
            array(26, 37.709425, 55.583983),
            array(27, 37.696256, 55.578834),
            array(28, 37.683167, 55.574019),
            array(29, 37.668911, 55.571999),
            array(30, 37.647765, 55.573093),
            array(31, 37.633419, 55.573928),
            array(32, 37.616719, 55.574732),
            array(33, 37.60107, 55.575816),
            array(34, 37.586536, 55.5778),
            array(35, 37.571938, 55.581271),
            array(36, 37.555732, 55.585143),
            array(37, 37.545132, 55.587509),
            array(38, 37.526366, 55.5922),
            array(39, 37.516108, 55.594728),
            array(40, 37.502274, 55.60249),
            array(41, 37.49391, 55.609685),
            array(42, 37.484846, 55.617424),
            array(43, 37.474668, 55.625801),
            array(44, 37.469925, 55.630207),
            array(45, 37.456864, 55.641041),
            array(46, 37.448195, 55.648794),
            array(47, 37.441125, 55.654675),
            array(48, 37.434424, 55.660424),
            array(49, 37.42598, 55.670701),
            array(50, 37.418712, 55.67994),
            array(51, 37.414868, 55.686873),
            array(52, 37.407528, 55.695697),
            array(53, 37.397952, 55.702805),
            array(54, 37.388969, 55.709657),
            array(55, 37.383283, 55.718273),
            array(56, 37.378369, 55.728581),
            array(57, 37.374991, 55.735201),
            array(58, 37.370248, 55.744789),
            array(59, 37.369188, 55.75435),
            array(60, 37.369053, 55.762936),
            array(61, 37.369619, 55.771444),
            array(62, 37.369853, 55.779722),
            array(63, 37.372943, 55.789542),
            array(64, 37.379824, 55.79723),
            array(65, 37.386876, 55.805796),
            array(66, 37.390397, 55.814629),
            array(67, 37.393236, 55.823606),
            array(68, 37.395275, 55.83251),
            array(69, 37.394709, 55.840376),
            array(70, 37.393056, 55.850141),
            array(71, 37.397314, 55.858801),
            array(72, 37.405588, 55.867051),
            array(73, 37.416601, 55.872703),
            array(74, 37.429429, 55.877041),
            array(75, 37.443596, 55.881091),
            array(76, 37.459065, 55.882828),
            array(77, 37.473096, 55.884625),
            array(78, 37.48861, 55.888897),
            array(79, 37.5016, 55.894232),
            array(80, 37.513206, 55.899578),
            array(81, 37.527597, 55.90526),
            array(82, 37.543443, 55.907687),
            array(83, 37.559577, 55.909388),
            array(84, 37.575531, 55.910907),
            array(85, 37.590344, 55.909257),
            array(86, 37.604637, 55.905472),
            array(87, 37.619603, 55.901637),
            array(88, 37.635961, 55.898533),
            array(89, 37.647648, 55.896973),
            array(90, 37.667878, 55.895449),
            array(91, 37.681721, 55.894868),
            array(92, 37.698807, 55.893884),
            array(93, 37.712363, 55.889094),
            array(94, 37.723636, 55.883555),
            array(95, 37.735791, 55.877501),
            array(96, 37.741261, 55.874698),
            array(97, 37.764519, 55.862464),
            array(98, 37.765992, 55.861979),
            array(99, 37.788216, 55.850257),
            array(100, 37.788522, 55.850383),
            array(101, 37.800586, 55.844167),
            array(102, 37.822819, 55.832707),
            array(103, 37.829754, 55.828789),
            array(104, 37.837148, 55.821072),
            array(105, 37.838926, 55.811599),
            array(106, 37.840004, 55.802781),
            array(107, 37.840965, 55.793991),
            array(108, 37.841576, 55.785017)
        );

        $points = array();

        foreach ($mkad_km as $km)
        {
            $points[] = new YApiPoint(null, $km[1], $km[2]);
        }

        $polygon = new YApiPolygon($points);

        return $polygon;
    }
}