<?php
namespace app\models;
use yii\db\ActiveRecord;


class Product extends ActiveRecord{

    public static function tableName(){    //определяем таблицу в бд
        return 'product';
    }

    public function getCategory(){
        return $this->hasOne(Category::className(), ['id' => 'category_id']);
    }

}