<?php
namespace app\models;
use yii\db\ActiveRecord;

class Category extends ActiveRecord{

    public static function tableName(){    //определяем таблицу в бд
        return 'category';
    }

    public function getProducts(){
        return $this->hasMany(Product::className(), ['category_id' => 'id']);  //определяем тип связи и связующие поля
    }

}