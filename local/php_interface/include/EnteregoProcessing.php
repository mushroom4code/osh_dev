<?php

namespace Enterego;

use Bitrix\Sale\Fuser;

class EnteregoProcessing
{

    private $db;
    public string $table_old_name = 'b_user_like';
    public string $table_new_name = 'ent_like_favorite';


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
                                        FROM $this->table_old_name group by product_id");

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


}
