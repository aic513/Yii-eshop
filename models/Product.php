<?php
/**
 * Created by PhpStorm.
 * User: aic513
 * Date: 15.02.2017
 * Time: 10:32
 */

namespace app\models;


class Product{

    public static function tableName(){    //определяем таблицу в бд
        return 'product';
    }

    public function getCategory(){
        return $this->hasMany(Category::className(), ['id' => 'category_id']);  //определяем тип связи и связующие поля
    }

}