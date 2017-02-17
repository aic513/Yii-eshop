<?php
namespace app\controllers;
use app\models\Product;
use app\models\Cart;
use app\models\Order;
use app\models\OrderItems;
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
        $session = Yii::$app->session;
        $session->open();
        $this->setMeta('Корзина');
        $order = new Order();
        if( $order->load(Yii::$app->request->post()) ){
            $order->qty = $session['cart.qty'];
            $order->sum = $session['cart.sum'];
            if($order->save()){        //если заказ сохранен
                $this->saveOrderItems($session['cart'], $order->id);
                Yii::$app->session->setFlash('success', 'Ваш заказ принят. Менеджер вскоре свяжется с Вами.');
                Yii::$app->mailer->compose('order', ['session' => $session])  //отправляем письмо на почту
                    ->setFrom(['aic513@mail.ru' => 'yii2.loc'])   //с какого email получается данная почта
                    ->setTo($order->email)  //email,который пользователь указал при заказе
                    ->setSubject('Заказ')
                    ->send();
                $session->remove('cart');
                $session->remove('cart.qty');
                $session->remove('cart.sum');
                return $this->refresh();
            }else{
                Yii::$app->session->setFlash('error', 'Ошибка оформления заказа');
            }
        }
        return $this->render('view', compact('session', 'order'));
    }

    protected function saveOrderItems($items, $order_id){  //получаем id сохраненной записи при оформлении заказа
        foreach($items as $id => $item){
            $order_items = new OrderItems();
            $order_items->order_id = $order_id;     //id заказа
            $order_items->product_id = $id;     //id товара
            $order_items->name = $item['name'];
            $order_items->price = $item['price'];
            $order_items->qty_item = $item['qty'];
            $order_items->sum_item = $item['qty'] * $item['price'];
            $order_items->save();
        }
    }


}