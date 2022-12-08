<?
namespace Skyweb24\Loyaltyprogram\Pub;
use \Bitrix\Main\Application,
	Bitrix\Main\Localization\Loc,
	\Skyweb24\Loyaltyprogram;
/**
* type dirthday 
*/
class Bonuses{
	
	public static function orderPayGetMaxBonuses(){
		$maxBonus = 0;
		$bonusPayClasses=\Skyweb24\Loyaltyprogram\Profiles\Profile::getActiveProfileByType('Orderpay');
		foreach($bonusPayClasses as $bonus){
			$bonusPay=\Skyweb24\Loyaltyprogram\Profiles\Profile::getProfileById($bonus);
			$pay=$bonusPay->getMaxBonus();
			if($pay>0&&$pay!==false){
				$maxBonus=$pay;
				break;
			}
		}
		//return $maxBonus;
		$settings=\Skyweb24\Loyaltyprogram\Settings::getInstance();
		$settingsOptions=$settings->getOptions();
		return [
				'BONUS'=>$maxBonus,
				'BONUS_FORMAT'=>\CurrencyFormat($maxBonus, $settingsOptions['currency']),
				'CURRENCY'=>$settingsOptions['currency']
			];
	}

	public static function orderingGetBonusBySumm($summ){
		if($summ>0){
			$orderingSumm=0;
			$activeProgramIds=\Skyweb24\Loyaltyprogram\Profiles\Profile::getActiveProfileByType('Ordering');
			$settings=\Skyweb24\Loyaltyprogram\Settings::getInstance();
			$settingsOptions=$settings->getOptions();
			foreach($activeProgramIds as $nextProgramId){
				$ordering=\Skyweb24\Loyaltyprogram\Profiles\Profile::getProfileById($nextProgramId);
				$nextSumm=$ordering->getBonusBySumm($summ);
				$orderingSumm+=$nextSumm;
				if($settingsOptions['ref_perform_all']=='N' && $nextSumm>0){
					break;
				}
			}
			$retArr=[
				'BONUS'=>$orderingSumm,
				'BONUS_FORMAT'=>\CurrencyFormat($orderingSumm, $settingsOptions['currency']),
				'CURRENCY'=>$settingsOptions['currency']
			];
			return $retArr;
		}
		return false;
	}
	
	public static function setBonusFromOuterSource($idUser, $summ=0){
		$isRun=false;
		$activeProgramIds=\Skyweb24\Loyaltyprogram\Profiles\Profile::getActiveProfileByType('Outersource');
		$settings=\Skyweb24\Loyaltyprogram\Settings::getInstance();
		$settingsOptions=$settings->getOptions();
		foreach($activeProgramIds as $nextProgramId){
			$source=\Skyweb24\Loyaltyprogram\Profiles\Profile::getProfileById($nextProgramId);
			$isRun=$source->setBonus($idUser, $summ);
			if($settingsOptions['ref_perform_all']=='N' && $isRun===true){
				break;
			}
		}
		return $isRun;
	}

}

?>