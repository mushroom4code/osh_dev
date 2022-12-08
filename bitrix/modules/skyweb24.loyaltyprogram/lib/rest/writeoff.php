<?php

namespace Skyweb24\Loyaltyprogram\Rest;

use \Bitrix\Rest\RestException,
    \Bitrix\Main\Localization\Loc,
    \Skyweb24\Loyaltyprogram,
    \Skyweb24\Loyaltyprogram\Entity;

\Bitrix\Main\Loader::includeModule('rest');
\Bitrix\Main\Loader::includeModule('sale');

Loc::loadMessages(__DIR__ .'/lang.php');
Loc::loadMessages(__FILE__);

class Writeoff extends \IRestService
{
    private static function getRequisites($userId){
        $requisites=[];
        if(!empty($userId)){
            $result=Entity\UserRequisitesTable::getList(['filter'=>['user_id'=>$userId, 'active'=>'Y']]);
            while ($arUser = $result->fetch()){
                $requisites[]=$arUser;
            }
        }
        return $requisites;
    }

    public static function writeOffList($params, $n, $server){
        $limit=(!empty($params['limit']) && $params['limit']<50)?$params['limit']:50;
        $offset=empty($params['offset'])?0:$params['offset'];
        $dataFilter=[
            'limit'=>$limit,
            'count_total' => true,
            'offset' => $offset,
        ];
        if(!empty($params['order'])){
            foreach($params['order'] as $keyOrder=>$valOrder){
                $dataFilter['order'][$keyOrder]=$valOrder;
            }
        }
        if(!empty($params['filter'])){
            foreach($params['filter'] as $keyOrder=>$valOrder){
                $clearKey=str_replace(['!', '=', '>', '<'], '', $keyOrder);
                if($clearKey=='date_order' || $clearKey=='date_change'){
                    $dataFilter['filter'][$keyOrder] = new \Bitrix\Main\Type\DateTime($valOrder);
                }else {
                    $dataFilter['filter'][$keyOrder] = $valOrder;
                }
            }
        }
        $data = Entity\WriteOffTable::getList($dataFilter);
        $result=[];
        $statuses=Entity\WriteOffTable::getStatuses();
        while ($arData = $data->fetch()) {
            $arData['date_change']=$arData['date_change']->toString();
            $arData['date_order']=$arData['date_order']->toString();
            $arData['status_text']=!empty($statuses[$arData['status']])?$statuses[$arData['status']]:$arData['status'];
            $result[]=$arData;
        }
        return $result;

        //throw new RestException('Unknown error');
    }

    public static function writeOffAdd($params, $n, $server){
        $errors=[];
        if(empty($params['user_id'])){
            $errors[]=Loc::getMessage("skyweb24.loyaltyprogram_writeoff_emptyuser");
        }
        if(empty($params['bonus']) || $params['bonus']<=0){
            $errors[]=Loc::getMessage("skyweb24.loyaltyprogram_writeoff_emptybonus");
        }
        if(count($errors)){
            throw new RestException(implode(', ',$errors));
        }else{
            $errors=[];
            $activeProgramIds=Loyaltyprogram\Profiles\Profile::getActiveProfileByType('Writeoff');
            foreach($activeProgramIds as $nextProgramId){
                $writeOffClass=Loyaltyprogram\Profiles\Profile::getProfileById($nextProgramId);
                $writeOffClass->setProperties(['idUser'=>$params['user_id']]);
                $bonusMax=$writeOffClass->getMaxBonus();
                $bonusMin=$writeOffClass->getMinBonus();
                if($bonusMin>$params['bonus']){
                    $errors[]=Loc::getMessage("skyweb24.loyaltyprogram_writeoff_smallbonus", ['#PROFILE_ID#'=>$nextProgramId]);
                }elseif($bonusMax<$params['bonus']){
                    $errors[]=Loc::getMessage("skyweb24.loyaltyprogram_writeoff_morebonus", ['#PROFILE_ID#'=>$nextProgramId]);
                }else{
                    $requisite=0;
                    $cReqisite=empty($params['requisites_id'])?0:$params['requisites_id'];
                    $requisites=self::getRequisites($params['user_id']);
                    foreach ($requisites as $nextRequisite) {
                        if($cReqisite==0 || $cReqisite==$nextRequisite['id']){
                            $requisite=$nextRequisite['id'];
                        }
                    }
                    if($requisite==0){
                       throw new RestException(', ', Loc::getMessage("skyweb24.loyaltyprogram_writeoff_invalid_requisite"));
                    }else{
                        $id=$writeOffClass->writeBonus($params['bonus'], $requisite);
                        if($id!=false){
                            return ['id'=>$id, 'status'=>'success'];
                        }
                    }
                }
            }
            $error=count($errors)>0?implode(', ',$errors):'Unknown error';
            throw new RestException($error);
        }
    }

    public static function writeOffUpdate($params, $n, $server){
        $errors=[];
        if(empty($params['id'])){
            $filterData['id']=$params['id'];
            $errors[]=Loc::getMessage("skyweb24.loyaltyprogram_writeoff_emptyuser");
        }
        if(empty($params['status']) || !in_array($params['status'], ['reject', 'execute'])){
            $errors[]=Loc::getMessage("skyweb24.loyaltyprogram_writeoff_emptystatus");
        }
        if(count($errors)>0){
            throw new RestException(implode(', ',$errors));
        }
        $result = Entity\WriteOffTable::getList(['filter' => ['id' => $params['id']]]);
        if ($arRes = $result->fetch()) {
            if($arRes['status']!='request'){
                throw new RestException(Loc::getMessage("skyweb24.loyaltyprogram_writeoff_requestisset"));
            }
            $updData=['status'=>$params['status']];
            if(!empty($params['comment'])){
                $updData['comment']=$params['comment'];
            }
            $updRes=Entity\WriteOffTable::update($params['id'], $updData);
            if (!$updRes->isSuccess()){
                throw new RestException(implode(', ',$updRes->getErrorMessages()));
            }else{
                return ['id'=>$params['id'], 'status'=>'success'];
            }
        }
        throw new RestException(Loc::getMessage("skyweb24.loyaltyprogram_writeoff_emptyrequest"));
    }

}