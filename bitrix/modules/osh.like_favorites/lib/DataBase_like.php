<?php

class  DataBase_like
{
    // TODO enterego - закешировать результаты выборки лайков для всех товаров

    /**
     * @param array $product_id
     * @param $FUser_id
     * @return array
     */
    public static function getLikeFavoriteAllProduct(array $product_id, $USER_ID, $FUser_id='' ): array
    {
        global $DB;
        $collection = [];
        $product_ids = implode(',', $product_id);

        if ($product_ids) {
            $sql = "SELECT I_BLOCK_ID , SUM(LIKE_USER) AS LIKE_USER FROM ent_like_favorite 
                    WHERE I_BLOCK_ID IN($product_ids) GROUP BY I_BLOCK_ID;";
            $result = $DB->Query($sql);
            while ($collectionElement = $result->Fetch()) {
                $id = $collectionElement['I_BLOCK_ID'];
                $collection['ALL_LIKE'][$id] = $collectionElement['LIKE_USER'];
            }
        }

        if (!empty($USER_ID)) {

            $sqlUser = "SELECT * FROM ent_like_favorite WHERE F_USER_ID = $USER_ID";
            $result_user_array = $DB->Query($sqlUser);
            while ($collectionElement_user = $result_user_array->Fetch()) {
                $id = $collectionElement_user['I_BLOCK_ID'];
                $collection["USER"][$id]['Like'][] = $collectionElement_user['LIKE_USER'];
                $collection["USER"][$id]['Fav'][] = $collectionElement_user['FAVORITE'];
				$collection["USER"]['NUM'] = $collection["USER"]['NUM'] + $collectionElement_user['FAVORITE'];
            }
        }
        if (!empty($FUser_id)) {

            $sqlUser = "SELECT * FROM ent_like_favorite WHERE F_USER_ID = $FUser_id";
            $result_user_array = $DB->Query($sqlUser);
            while ($collectionElement_user = $result_user_array->Fetch()) {
                $id = $collectionElement_user['I_BLOCK_ID'];
                $collection["USER"][$id]['Like'][] = $collectionElement_user['LIKE_USER'];

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
    public static function SetRemoveLikeFavorite($USER_ID='', $product_id, $value, $method, $Like_user_id=''): bool
    {

        global $DB;

        $sql = '';
		$METHOD = '';

		if (!empty($method) && !empty($product_id)) {
            if ($method === 'like' && !empty($Like_user_id) ) {
                $METHOD = 'LIKE_USER';
				$CheckTable = "SELECT * FROM ent_like_favorite 
							   WHERE F_USER_ID = $Like_user_id AND I_BLOCK_ID=$product_id";
				$resultSelect = $DB->Query($CheckTable);
				


				if (!$resultSelect->Fetch()) {
					$sql = "INSERT INTO ent_like_favorite (F_USER_ID,I_BLOCK_ID,$METHOD)
									VALUES ($Like_user_id,$product_id,$value);";
				} else {
					$sql = "UPDATE  ent_like_favorite  SET $METHOD=$value  
								WHERE F_USER_ID = $Like_user_id AND I_BLOCK_ID=$product_id";
				}				
				
            } elseif ($method === 'favorite' && !empty($USER_ID) ) {
                $METHOD = 'FAVORITE';
            
				$CheckTable = "SELECT * FROM ent_like_favorite 
							   WHERE F_USER_ID = $USER_ID AND I_BLOCK_ID=$product_id";
				$resultSelect = $DB->Query($CheckTable);
				


				if (!$resultSelect->Fetch()) {
					$sql = "INSERT INTO ent_like_favorite (F_USER_ID,I_BLOCK_ID,$METHOD)
									VALUES ($USER_ID,$product_id,$value);";
				} else {
					$sql = "UPDATE  ent_like_favorite  SET $METHOD=$value  
								WHERE F_USER_ID = $USER_ID AND I_BLOCK_ID=$product_id";
				}			
			}
		
        
			$DB->Query($sql);

			return true;		
		}
		
		


     


    }

}
