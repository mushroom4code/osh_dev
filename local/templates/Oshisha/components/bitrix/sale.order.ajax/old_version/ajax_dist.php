<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");
ini_set('display_errors', '0');
ini_set('display_startup_errors', '0');
#error_reporting(E_ALL & ~E_NOTICE);
use Mrden\MkadDistance\Distance;
/*spl_autoload_register(function ($class_name) {
    include $_SERVER["DOCUMENT_ROOT"] .'/local/libs/distance/'$class_name . '.php';
});*/
require($_SERVER["DOCUMENT_ROOT"]."/local/libs/php-osrm/autoload.php");

CModule::AddAutoloadClasses(
    "",
    array(
        "Mrden\MkadDistance\Distance"  => "/local/libs/distance/Distance.php",
		'Mrden\MkadDistance\Strategy\StrategyFactory'   => "/local/libs/distance/Strategy/StrategyFactory.php",
		'Mrden\MkadDistance\Geometry\Polygon\MscMkad' => "/local/libs/distance/Geometry/Polygon/MscMkad.php",
		'Mrden\MkadDistance\Geometry\Polygon\MscMkadJunctions' => "/local/libs/distance/Geometry/Polygon/MscMkadJunctions.php",
		'Mrden\MkadDistance\Geometry\Polygon' => "/local/libs/distance/Geometry/Polygon.php",
		'Mrden\MkadDistance\Geometry\Point' => "/local/libs/distance/Geometry/Point.php",
		'Mrden\MkadDistance\Geometry\DistanceBetweenPoints' => "/local/libs/distance/Geometry/DistanceBetweenPoints.php",
		'Mrden\MkadDistance\Geometry\InvalidArgumentException' => "/local/libs/distance/InvalidArgumentException.php",
		'Mrden\MkadDistance\Iterface\DistanceCalculatorStrategy' => "/local/libs/distance/Iterface/DistanceCalculatorStrategy.php",
		'Mrden\MkadDistance\Strategy\AddressDistanceCalculator'  => "/local/libs/distance/Strategy/AddressDistanceCalculator.php",
		'Mrden\MkadDistance\Strategy\PointDistanceCalculator'  => "/local/libs/distance/Strategy/PointDistanceCalculator.php",
		'Mrden\MkadDistance\Strategy\AbstractDistanceCalculate'  => "/local/libs/distance/Strategy/AbstractDistanceCalculate.php",
		'Mrden\MkadDistance\Strategy\ArrayDistanceCalculator'  => "/local/libs/distance/Strategy/ArrayDistanceCalculator.php",
		  'Yandex\\Geo\\Api' => '/local/libs/Yandex/Geo/Api.php',
		  'Yandex\\Geo\\Exception' => '/local/libs/Yandex/Geo/Exception.php',
		  'Yandex\\Geo\\Exception\\CurlError' => '/local/libs/Yandex/Geo/Exception/CurlError.php',
		  'Yandex\\Geo\\Exception\\ServerError' => '/local/libs/Yandex/Geo/Exception/ServerError.php',
		  'Yandex\\Geo\\GeoObject' => '/local/libs/Yandex/Geo/GeoObject.php',
		  'Yandex\\Geo\\Response' => '/local/libs/Yandex/Geo/Response.php',
		'Mrden\MkadDistance\Exception\InnerPolygonException'  => "/local/libs/distance/Exception/InnerPolygonException.php",
		'Mrden\MkadDistance\Exception\DistanceException'  => "/local/libs/distance/Exception/DistanceException.php",

		
	
	)
	
    );


$arPoligonVtPt = [
[37.6541779919487,55.575594844802225],
[37.7640412731987,53.83594665170766],
[39.9942658825737,54.59889673478215],
[40.779788343511186,55.687469126033776],
[40.73313067857851,56.11060901209446],
[40.584815248891,56.60462751428465],
[37.827246889516,55.799348071534546],
[37.81626056139096,55.63189867125496],
[37.6541779919487,55.575594844802225]
];

$arPoligonPnCht = [
[37.829113536614244,55.80752264082354],
[40.625134044426765,56.618689111245395],
[38.85084205223926,57.49563188074694],
[38.13931564113693,57.57412110022727],
[35.821200406761925,57.45884202075128],
[34.66214278957443,56.937047385907526],
[37.37027267238692,55.852561024919254],
[37.829113536614244,55.80752264082354]
];

$arPoligonSrSb = [
[37.6590428068063,55.56321112066703],
[37.41455470538854,55.84403729885284],
[34.662479510076025,56.952784493965254],
[34.17608664280016,56.31933444526636],
[34.20904562717518,55.245243907726106],
[34.85932043185867,54.60913578266341],
[37.75971105685867,53.8073796199664],
[37.6590428068063,55.56321112066703]
];
/*
$arNightCity = array(
'Зеленоград',
'Щербинка',
'Химки',

)*/

$address = $_POST['query'];

 

$ch = curl_init('https://geocode-maps.yandex.ru/1.x/?apikey=0f193f7e-378f-47b0-a6e5-665f01740510&format=json&geocode=' . urlencode($address));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_HEADER, false);
$res = curl_exec($ch);
curl_close($ch);

$res = json_decode($res, true);
$coordinates = $res['response']['GeoObjectCollection']['featureMember'][0]['GeoObject']['Point']['pos'];
$coordinates = explode(' ', $coordinates);
$point[0] = $coordinates[1]; 
$point[1] = $coordinates[0];
 
//print_r($point);

try {
	$distance = Distance::createMoscowMkadCalculator(
		$point,
		//['yandexGeoCoderApiKey' => '0f193f7e-378f-47b0-a6e5-665f01740510']   
	)->calculate();	
	if( intval( $distance )  > 0 )
		$result['distance'] = $distance;

		//Если расстояние меньше 200 определим где оно
		if( $distance < 200 )
		{	
			if( isInnerCheck($coordinates, $arPoligonVtPt) === true )
				$result['type'] = 'VtPt';
			elseif( isInnerCheck($coordinates, $arPoligonPnCht) === true  )
				$result['type'] = 'PnCht';
			elseif( isInnerCheck($coordinates, $arPoligonSrSb) === true  )
				$result['type'] = 'SrSb';		
		}
	echo json_encode($result);
	
} catch (Exception $e){
    echo $e->getMessage();
}








    function isInnerCheck($point, $arPoligon): bool
    {
        // Check if the point sits exactly on a vertex
        if( IsCorner( $point, $arPoligon ) )
			return true;


        // Check if the point is inside the polygon or on the boundary
        $intersections = 0;
        $vertices_count = count($arPoligon);

        for ($i = 1; $i < $vertices_count; $i++) {
            $vertexPrev = $arPoligon[$i - 1];
            $vertexCur = $arPoligon[$i];
            if ($vertexPrev[0] == $vertexCur[0] && $vertexPrev[0] == $point[0] &&
                $point[1] > min($vertexPrev[1], $vertexCur[1]) &&
                $point[1] < max($vertexPrev[1], $vertexCur[1])
            ) { // Check if point is on an horizontal polygon boundary
                return true;
            }
            if ($point[0] > min($vertexPrev[0], $vertexCur[0]) &&
                $point[0] <= max($vertexPrev[0], $vertexCur[0]) &&
                $point[1] <= max($vertexPrev[1], $vertexCur[1]) &&
                $vertexPrev[0] != $vertexCur[0]
            ) {
                $xinters = ($point[0] - $vertexPrev[0]) * ($vertexCur[1] - $vertexPrev[1]) / ($vertexCur[0] - $vertexPrev[0]) + $vertexPrev[1];
                if ($xinters == $point[1]) { // Check if point is on the polygon boundary (other than horizontal)
                    return true;
                }
                if ($vertexPrev[1] == $vertexCur[1] || $point[1] <= $xinters) {
                    $intersections++;
                }
            }
        }
        // If the number of edges we passed through is odd, then it's in the polygon.
        return $intersections % 2 != 0;
    }
	
	function IsCorner( $point, $arPoligon )
	{
		foreach( $arPoligon as $poligon )
		{
			if($poligon[0] == $point[0] && $poligon[1] == $point[1])
				return true;
		}
	}
	

?>