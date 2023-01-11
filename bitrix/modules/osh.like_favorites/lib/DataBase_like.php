<?php

use Bitrix\Main\Entity;

class  DataBase_like
{
    // TODO enterego - закешировать результаты выборки лайков для всех товаров

    /**
     * @param array $product_id
     * @param $FUser_id
     * @return array
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     */
    public static function getLikeFavoriteAllProduct(array $product_id, $FUser_id): array
    {

        $collection = [];
        $product_ids = implode(',', $product_id);

        if ($product_ids) {
            $resultSelect = Bitrix\Like\ORM_like_favoritesTable::getList(
                array(
                    'select' => array(
                        'I_BLOCK_ID', 'LIKE_USERS'
                    ),
                    'filter' => array(
                        '@I_BLOCK_ID' => $product_id,
                    ),
                    'group' => array('I_BLOCK_ID'),
                )
            );


            while ($collectionElement = $resultSelect->Fetch()) {
                $id = $collectionElement['I_BLOCK_ID'];
                $collection['ALL_LIKE'][$id] = $collectionElement['LIKE_USERS'];
            }
        }

        if (!empty($FUser_id)) {

            $result_user_array = Bitrix\Like\ORM_like_favoritesTable::getList(array('filter' => [
                'F_USER_ID' => $FUser_id,
            ]));

            while ($collectionElement_user = $result_user_array->fetch()) {
                $id = $collectionElement_user['I_BLOCK_ID'];
                $collection["USER"][$id]['Like'][] = $collectionElement_user['LIKE_USER'];
                $collection["USER"][$id]['Fav'][] = $collectionElement_user['FAVORITE'];
            }
        }

        return $collection;

    }

    /**
     * @param $FUser_id
     * @param $product_id
     * @param $value
     * @param $method
     * @return bool
     */
    public static function SetRemoveLikeFavorite($FUser_id, $product_id, $value, $method): bool
    {

        if (!empty($method) && !empty($FUser_id) && !empty($product_id)) {
            $resultSelect = Bitrix\Like\ORM_like_favoritesTable::getList(array('filter' => [
                'I_BLOCK_ID' => $product_id,
                'F_USER_ID' => $FUser_id,
            ]));

            $METHOD = '';

            if ($method === 'like') {
                $METHOD = 'LIKE_USER';
            } elseif ($method === 'favorite') {
                $METHOD = 'FAVORITE';
            }

            if (!$resultSelect->Fetch()) {
                Bitrix\Like\ORM_like_favoritesTable::add(
                    array(
                        $METHOD => $value,
                        'I_BLOCK_ID' => $product_id,
                        'F_USER_ID' => $FUser_id
                    ),
                );
            } else {
                Bitrix\Like\ORM_like_favoritesTable::update(
                    array(
                        'I_BLOCK_ID' => $product_id,
                        'F_USER_ID' => $FUser_id
                    ),
                    array(
                        $METHOD => $value
                    )
                );
            }
        }

        return true;
    }

}
