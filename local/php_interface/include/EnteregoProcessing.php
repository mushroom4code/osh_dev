<?php

namespace Enterego;

use Bitrix\Sale\Fuser;

class EnteregoProcessing
{

    private $db;
    public string $table_old_name_like = 'b_user_like';
    public string $table_old_name_fav = 'b_utm_user';
    public string $table_new_name = 'ent_like_favorite';
    public int $prop_favorites_id = 41;

    /**
     * @return string
     * @throws \Exception
     */
    public function update_like_in_new_table(): string
    {
        global $DB;
        $count = 0;
        $this->db = $DB;
        $res = $this->db->Query("SELECT count(distinct id) count, product_id 
                                        FROM $this->table_old_name_like group by product_id");

        while ($arData = $res->Fetch()) {
            $count++;
            $product_id = (int)$arData['product_id'];
            $arFields['LIKE_USER'] = 1;
            $arFields['I_BLOCK_ID'] = $product_id;
            $arFields['FAVORITE'] = 0;

            for ($i = 0; $i < (int)$arData['count']; $i++) {
                $FUser_id = random_int(1, 10000);
                $arFields['F_USER_ID'] = $FUser_id;
                $this->db->Insert($this->table_new_name, $arFields);
            }
        }

        return 'export ' . $count;
    }


    /**
     * @return string
     * @throws \Exception
     */
    public function update_favorites_product_users(): string
    {

        global $DB;
        $count = 0;
        $this->db = $DB;
        $users_ar = $this->db->Query("SELECT VALUE_ID user_id, VALUE_INT product_id 
                                            FROM $this->table_old_name_fav 
                                            WHERE FIELD_ID=$this->prop_favorites_id");


        while ($arData = $users_ar->Fetch()) {

            $count++;
            $FUser_id = Fuser::getIdByUserId($arData['user_id']);
            $product_id = (int)$arData['product_id'];

            $row_res = $this->db->Query("SELECT I_BLOCK_ID product_id  FROM $this->table_new_name
            WHERE F_USER_ID=$FUser_id AND I_BLOCK_ID=$product_id");
            $result = $row_res->Fetch();

            $arFields['FAVORITE'] = 1;

            if (!empty($result['product_id']) && $result !== false) {
                $this->db->Update($this->table_new_name, $arFields,
                    "WHERE F_USER_ID=$FUser_id AND I_BLOCK_ID=$product_id");
            } else {
                $arFields['F_USER_ID'] = $FUser_id;
                $arFields['I_BLOCK_ID'] = $product_id;
                $arFields['LIKE_USER'] = 0;
                $this->db->Insert($this->table_new_name, $arFields);
            }

        }

        return 'export ' . $count;
    }

}
