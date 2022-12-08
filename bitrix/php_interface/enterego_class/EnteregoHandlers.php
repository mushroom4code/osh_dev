<?php

use Enterego\EnteregoHelper;

$act = $_POST['product_id'];
if(!empty($act)){
    echo $act;
  return EnteregoHelper::update($act);
}
