<?
namespace Skyweb24\Loyaltyprogram;
interface Profileinterface{
	
	function getParametersMain();
	function getParametersBonuses();
	//function setBonus($userId);
	function save($params);
	
}
?>