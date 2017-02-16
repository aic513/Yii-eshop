<?php
namespace app\controllers;
use app\models\Product;
use app\models\Cart;
use Yii;

/*Array
(
    [1] => Array
    (
        [qty] => QTY
        [name] => NAME
        [price] => PRICE
        [img] => IMG
    )
    [10] => Array
    (
        [qty] => QTY
        [name] => NAME
        [price] => PRICE
        [img] => IMG
    )
)
    [qty] => QTY,  //кол-во
    [sum] => SUM  //сумма
);*/

class CartController extends AppController{

    public function actionAdd(){  //добавление товара в корзину
        $id = Yii::$app->request->get('id');  //записываем id товара
        $qty = (int)Yii::$app->request->get('qty');
        $qty = !$qty ? 1 : $qty;
        $product = Product::findOne($id);  //находим товар в бд
        if(empty($product)) return false;
        $session =Yii::$app->session;
        $session->open();  //открываем сессию
        $cart = new Cart();  // создаем объект модели Cart
        $cart->addToCart($product,$qty);
        if( !Yii::$app->request->isAjax ){
            return $this->redirect(Yii::$app->request->referrer);
        }
        $this->layout = false;  //чтобы не подключался шаблон
        return $this->render('cart-modal', compact('session'));  //передаем в шаблон
    }

    public function actionClear(){   //очистка корзины
        $session =Yii::$app->session;
        $session->open();
        $session->remove('cart');
        $session->remove('cart.qty');
        $session->remove('cart.sum');
        $this->layout = false;
        return $this->render('cart-modal', compact('session'));
    }

    public function actionDelItem(){   //удаление 1-го товара
        $id = Yii::$app->request->get('id');
        $session =Yii::$app->session;
        $session->open();
        $cart = new Cart();
        $cart->recalc($id);
        $this->layout = false;
        return $this->render('cart-modal', compact('session'));
    }

    public function actionShow(){  //показать корзину
        $session =Yii::$app->session;
        $session->open();
        $this->layout = false;
        return $this->render('cart-modal', compact('session'));
    }

    public function actionView(){
        return $this->render('view');
    }


}