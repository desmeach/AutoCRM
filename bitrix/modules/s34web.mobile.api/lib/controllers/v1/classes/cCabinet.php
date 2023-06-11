<?php
namespace s34web\Mobile\Api\controllers\v1\classes;

use Bitrix\Main\Config\Option;
use Bitrix\Sale\Payment;

/**
 * Dev: Alex Rilkov
 *
 * Класс реализует функции подсистемы Личный кабинет
 * Список функций раздела
 * 1. Получение местоположений по фильтру (готово)
 * 2. Изменение данных профиля (фамилия, имя, отчество, телефон, дата рождения (после ввода не меняется))
 * 3. Список заказов (Номер заказа, сумма заказа, дата создания, статус оплаты, статус заказа, тип доставки, адрес магазина для самовывоза, адрес покупателя для заказа курьером, список товаров с иконками). Сортировка заказов по дате добавления.
 * 4. Карточка заказа (слайдер по товарам, картинка (до 500 пикселей по самой узкой стороне. соотношение, примерное - высота = 1.20 * ширины), название с торговыми предложениями и цены)
 *
 */

class cCabinet
{
  const DEFAULT_PAGE_COUNT = 10;
  const DEFAULT_ORDERLIST_CACHE = 360;

  public static function getOrderList(int $user_id, $page=1, $page_count=self::DEFAULT_PAGE_COUNT)
    {
        $result = new ResultData();
        $params = [
          'filter'  =>  ["USER_ID" => $user_id, "!=STATUS_ID"=> "F"],
          'order'   =>  ['DATE_UPDATE' => 'DESC'],
          "cache"   =>  ["ttl" => self::DEFAULT_ORDERLIST_CACHE]
        ];
        if((int)$page_count<=0) {
          $page_count = self::DEFAULT_PAGE_COUNT;
        }

        if(((int)$page)<1)
          $page = 1;

        $params['limit']=$page_count;
        $params['offset']=($page-1)*$page_count;

        $orders = $dbRes = \Bitrix\Sale\Order::loadByFilter($params);

        $data = [];
        foreach ($orders as $order)
        {
            $data[] = self::getOrderData($order);
        }

        if (!$data['error'] && !$data['error_message'])
        {
            $result->setData($data);
//          $result->setData($orders[0]->getFieldValues());
        } else {
            $result->setErrors($data['error'], $data['error_message']);
        }

        return $result;
    }

    public static function getProfileData(int $user_id)
    {
        $result = new ResultData();

        if ( $user_data = \Bitrix\Main\UserTable::getList(
            array(
//            "select"=>Array("NAME", "LAST_NAME", "SECOND_NAME", "PERSONAL_BIRTHDAY", "PERSONAL_PHONE", "PHONE_NUMBER" => "USER.PHONE_NUMBER"),
                "select" => array("NAME", "LAST_NAME", "SECOND_NAME", "PERSONAL_BIRTHDAY", "PERSONAL_PHONE",'UF_LOCATION_ID'),
                "filter" => ["=ID" => $user_id]
            )
        )->fetch()) {
            $phone_data = \Bitrix\Main\UserPhoneAuthTable::getList(
                $parameters = array(
                    'filter' => array('USER.ID' => $user_id)
                )
            )->fetch();

            $user_data["PHONE_NUMBER"] = $phone_data["PHONE_NUMBER"];
            $convertDictionary = [
                'NAME' => ['new_key'=>'name', 'data_type'=>'stringNotNull'],
                'LAST_NAME' => ['new_key'=>'last_name', 'data_type'=>'stringNotNull'],
                'SECOND_NAME' => ['new_key'=>'second_name', 'data_type'=>'stringNotNull'],
                'PERSONAL_BIRTHDAY' => ['new_key' => 'birthday', 'data_type' => 'date_ymd'],
                'PHONE_NUMBER' => 'phone',
            ];

            $data = cGeneral::convertDataElement($user_data, $convertDictionary);

            $loc_data = cLocations::getLocationByCode($user_data['UF_LOCATION_ID']);
            if($loc_data)
            {
                $data["location"] = [
                    'id' => $loc_data["ID"],
                    'city' => $loc_data["CITY"],
                    'full_name' => $loc_data["FULL_NAME"]
                ];
            }else{
                $data["location"] = null;
            }

            if (!$data['error'] && !$data['error_message']) {
                $result->setData($data);
            } else {
                $result->setErrors($data['error'], $data['error_message']);
            }
        }else{
            $result->setStatusNotFoundError();
        }

        return $result;
    }

    /**
     * @param int $user_id
     * @param $second_name
     * @param $name
     * @param $last_name
     * @param $phone
     * @param $birthday
     * @return ResultData
     */
    public static function setProfileData(int $user_id,$second_name,$name,$last_name,$phone,$birthday,$location_id)
    {
        $result = new ResultData();
        $data = [];
        $is_valid = false;
        if(strlen($name)>0) {
          $data["result"]["NAME"] = $name;
          $is_valid = true;
        }else
        {
          $data["result"]["NAME"] = "";
        }

        if(strlen($second_name)>0) {
            $data["result"]["SECOND_NAME"] = $second_name;
            $is_valid = true;
        }else{
          $data["result"]["SECOND_NAME"] = "";
        }

        if(strlen($last_name)>0){
            $data["result"]["LAST_NAME"] = $last_name;
            $is_valid = true;
        }else{
          $data["result"]["LAST_NAME"] = "";
        }

        if(cGeneral::isNameInOne()
          && strlen( $data["result"]["NAME"])
          && !strlen( $data["result"]["SECOND_NAME"])
          && !strlen( $data["result"]["LAST_NAME"]))
        {
          $data["result"]["LAST_NAME"] = " ";
        }

        if(!$is_valid){
            $data['error'][] = cErrors::INCORRECT_NAME_DATA;
        }

        if(strlen($phone))
        {
            if(substr($phone,0,1) != "+")
                $phone="+".trim($phone);
            if(self::checkPhone($phone)) {
                $data["result"]["PHONE_NUMBER"] = $phone;
                $data["result"]["PERSONAL_PHONE"] = cGeneral::formatPhone($phone);
            }
            else
                $data['error'][] = cErrors::INCORRECT_PHONE;
        }

        if(strlen($birthday))
        {
            if(cGeneral::BIRTHDAY_UPDATE_IS_LOCKED && !self::isAvailableBirthdaySetup($user_id, $birthday)) {
                $data['error'][] = cErrors::BIRTHDAY_UPDATE_LOCKED;
            }
            elseif (cGeneral::validateDate($birthday, "Ymd")) {
                $phpDate = \DateTime::createFromFormat("Ymd", $birthday);
                $data["result"]["PERSONAL_BIRTHDAY"] = \Bitrix\Main\Type\DateTime::createFromPhp($phpDate);
            }
            else
                $data['error'][] = cErrors::getText(cErrors::INCORRECT_DATE_FORMAT, ["FORMAT" => "YYYYMMDD"]);

        }

        if(strlen($location_id))
        {
            //@TODO: Проверить в базе местоположений
            $data["result"]["UF_LOCATION_ID"] = $location_id;
        }

        if(!empty($data["result"])) {
            if(empty($data['error'])) {
                $user = new \CUser();
                if (!$user->Update($user_id, $data["result"])) {
                    if(!empty($user["LAST_ERROR"]))
                    {
                        //Пользователь не найден.
                        $result->setStatusNotFoundError();
                        return $result;
                    }else {
                        //Ошибка проверки данных
                        $data['error'] = -1;
                        $data['error_message'] = $user["LAST_ERROR"];
                    }
                }
            }
        }

        if (!$data['error'] && !$data['error_message']) {
            $result->setSuccess();
        } else {
            $result->setErrors($data['error'], $data['error_message']);
        }

        return $result;
    }

    /**
     * Проверка номера телефона на валидность
     * Формат: +79991234567
     *
     * @param $phone
     * @return bool
     */
    private static function checkPhone($phone)
    {
        return strlen($phone)==12 && preg_match('#^\+[0-9]+$#', $phone);
    }

    private static function isAvailableBirthdaySetup($user_id, $newBirthday)
    {
        $birthday = \Bitrix\Main\UserTable::getList([
            "select"=>Array("PERSONAL_BIRTHDAY"),
            "filter"=>["=ID"=>$user_id]
        ])->fetch()["PERSONAL_BIRTHDAY"];
        return !isset($birthday) || $birthday->format("Ymd") == $newBirthday;
    }

    private static function getOrderData(\Bitrix\Sale\Order $order)
    {
        $data =[];

        $data["id"] = $order->getId(); // ID заказа
//        $data["createDate"] = $order->getDateInsert(); // объект Bitrix\Main\Type\DateTime

        $data["total_price"] = cGeneral::priceFormatNumber($order->getPrice()); // Сумма заказа
        $data["discount_price"] = cGeneral::priceFormatNumber($order->getDiscountPrice()); // Размер скидки
        $data["delivery_price"] = cGeneral::priceFormatNumber($order->getDeliveryPrice()); // Стоимость доставки

        $fields = ['ACCOUNT_NUMBER', 'DATE_INSERT', 'STATUS_ID', 'PAY_SYSTEM_ID', 'DELIVERY_ID'];
        $convertDictionary = [
//          'ID' =>                 ['new_key' => 'orderId',        'data_type' => 'int'],
            'ACCOUNT_NUMBER' =>     ['new_key' => 'order_number',   'data_type' => 'string'],
            //'PRICE' =>            ['new_key' => 'totalPrice',     'data_type' => 'float'],
//          'DISCOUNT_VALUE' =>     ['new_key' => 'discountValue',  'data_type' => 'float'],
            'DATE_INSERT' =>        ['new_key' => 'create_date',    'data_type' => 'date_utc0'],
            'DATE_UPDATE' =>        ['new_key' => 'update_date',    'data_type' => 'date_utc0'],
            'STATUS_ID' =>          ['new_key' => 'status',         'data_type' => 'orderStatus'],
            'PAY_SYSTEM_ID' =>      ['new_key' => 'pay_system',     'data_type' => 'orderPaySystem'],
//          'PAYED' =>              ['new_key' => 'payed',          'data_type' => 'bool'],
            'DELIVERY_ID' =>        ['new_key' => 'delivery',       'data_type' => 'orderDelivery'],
        ];
        $orderFields =  $order->getFieldValues();

      $data["is_paid"] = $order->isPaid(); // true, если оплачен
      $data["is_online_payment"] = self::isOnlinePayment($order);//проверка доступности оплаты
      //        $data["id"] = $order->isAllowDelivery(); // true, если разрешена доставка
      //$data["is_shipped"] = $order->isShipped(); // true, если отправлен
      $data["is_canceled"] = $order->isCanceled() || in_array($order->getField("STATUS_ID"), ["D"]); // true, если отменен или статус отмены

      $data = array_merge($data, cGeneral::convertDataElement($orderFields, $convertDictionary));

      $propertyCollection = $order->getPropertyCollection();

      if($address = $propertyCollection->getAddress()) {
            $data["delivery"]["address"] = $address->getValue();
            $data["delivery"]["is_pickup"] = false;
      }else {
            $shipmentCollection = $order->getShipmentCollection();
            foreach ($shipmentCollection as $shipment) {
                if (!$shipment->isSystem()) {
                    $storeId = $shipment->getStoreId();
                    break;
                }
            }
            if ($storeId > 0) {
                $arStore = \Bitrix\Catalog\StoreTable::getRow([
                                                                  'select' => ['ADDRESS'],
                                                                  'filter' => [
                                                                      'ID' => $storeId,
                                                                  ]
                                                              ]);
                $data["delivery"]["store_id"] = $storeId;
                $data["delivery"]["address"] = $arStore['ADDRESS'];
                $data["delivery"]["is_pickup"] = $storeId>0;
            }
        }

        $basket = $order->getBasket();
        $basketCollection = $basket->getBasketItems();
        $productsConvertDictionary = [
            'id' =>           ['data_type' => 'int'],
            'quantity' =>     ['data_type' => 'float'],
        ];

        $productIds =[];
        $parentProductIds = [];
        foreach ($basketCollection as $basketItem)
        {
            $product = [];
            $product["id"] = $basketItem->getField("PRODUCT_ID");
            $nameData = self::parseProductName($basketItem->getField("NAME"));
            $product["name"] = $nameData["name"];
            $product["parameters"] = $nameData["parameters"];
            $product["price"] = cGeneral::priceFormatNumber($basketItem->getField("PRICE"));
            $product["quantity"] = $basketItem->getField("QUANTITY");
            $product["total_price"] = cGeneral::priceFormatNumber($product["price"] * $product["quantity"]);
            $product["in_stock"] = false;

            $productIds[] = $product["id"];
            if ($parentProductId = \CCatalogSku::GetProductInfo($product["id"], cGeneral::getIBlockIDByLang("offers"))["ID"]) {
                $parentProductIds[$parentProductId] = $product["id"];
                $productIds[] = $parentProductId;
            }
            $data["products"][$product["id"]] = array_merge($product, cGeneral::convertDataElement($product, $productsConvertDictionary));
        }

        $productsDbRes = \Bitrix\Catalog\ProductTable::getList([
            'select' => ["ID", 'PREVIEW_PICTURE' => 'IBLOCK_ELEMENT.PREVIEW_PICTURE', 'IBLOCK_ID' => 'IBLOCK_ELEMENT.IBLOCK_ID', 'NAME' => 'IBLOCK_ELEMENT.NAME'],
            'filter' => ["=ID" => $productIds, "=IBLOCK_ELEMENT.ACTIVE" => true]
        ]);

        while ($product = $productsDbRes->fetch())
        {
            if($data["products"][$product["ID"]])
            {
                $data["products"][$product["ID"]]["in_stock"] = true;
                $data["products"][$product["ID"]]["picture_url"] = $product["PREVIEW_PICTURE"] > 0 ? cGeneral::getFullPath(
                    \CFile::GetFileArray($product["PREVIEW_PICTURE"])["SRC"]
                ) : null;;
            }
            elseif ($offerId = $parentProductIds[$product["ID"]])
            {
                $data["products"][$offerId]["parent_product_id"] = (int)$product["ID"];
                //$data["products"][$offerId]["parentProductName"] = $product["NAME"];
            }
        }
        $data["products"] = array_values($data["products"]);

//        foreach ($data["products"] as $id => $product)
//        {
//            if($products[$id])
//            {
//                $data["products"][$id]["inStock"] = true;
//                $data["products"][$id]["picture_url"] = $products[$id]["PREVIEW_PICTURE"] > 0 ? General::getFullPath(
//                    \CFile::GetFileArray($products[$id]["PREVIEW_PICTURE"])["SRC"]
//                ) : null;;
//
//                $data["products"][$id]["IBLOCK_ID"] = $products[$id]["IBLOCK_ID"];
//            }
//            elseif($productsWithOffersIds[$id])
//            {
//                $data["products"][$productsWithOffersIds[$id]]["parentProductId"] = $id;
//                $data["products"][$productsWithOffersIds[$id]]["parentName"] = $products[$id]["NAME"];
//                $data["products"]["TEST"] = $productsWithOffersIds[$id];
//            }
//            else
//            {
//                $data["products"][$id]["inStock"] = false;
//            }
//        }
//        $data = $order->getFieldValues();
//        $data = General::convertDataElement($data, $convertDictionary);

        return $data;
    }

    private static function parseProductName($offerName)
    {
        $product = ["name"=>$offerName,"parameters"=>""];

        $pos = strrpos($offerName," (");
        if($pos>4)
        {
            $product["name"] = trim(substr($offerName,0,$pos));
            $pos2 = strrpos($offerName,")",$pos+2);
            if ($pos2>2) {
                $product["parameters"] = trim(substr($offerName,$pos+2,$pos2-$pos-2));
            }
        }
        return $product;
    }

    private static function getOrderFields($order, array $fields)
    {
        $result = [];
        foreach ($fields as $field)
        {
            $result[$field] = $order->getField($field);
        }
        return $result;
    }

    private static function getAllowPaymentStatuses()
    {
      static $payment_statuses = false;
      if(!$payment_statuses)
      {
        $arps = \Bitrix\Sale\OrderStatus::getAllowPayStatusList();
        foreach($arps as $arps_item)
        {
          //Начиная я начального статуса оплаты и кроме Отменён и Выполнен
          if(!in_array($arps_item,["D","F"]) )
            $payment_statuses[] = $arps_item;
        }
        /*$payment_statuses = [];
        $statusResult = \Bitrix\Sale\Internals\StatusTable::getList(array(
          'order'  => ['SORT' => 'ASC'],
          'filter' => ['TYPE' => 'O'],
          'select' => ["ID"]
        ));

        while ($status = $statusResult->fetch()) {
          if ($status["ID"] == "P") {
            break;
          } else
            $payment_statuses[] = $status["ID"];

        }*/
      }
      return $payment_statuses;
    }

    private static function isOnlinePayment(\Bitrix\Sale\Order $order)
    {
      $paymentsCollection = $order->getPaymentCollection();
      /** @var Payment $payment */
      foreach ($paymentsCollection as $payment)
        {
          $id = $payment->getField("PAY_SYSTEM_ID");
          if(!$payment->isInner() && in_array($id,cOrders::PAYMENTS_LIST))
          {

            $rsPaySystem = \Bitrix\Sale\Internals\PaySystemActionTable::getList(array(
              'filter' => array('ID'=>$id/*,"ACTIVE"=>"Y",*/),
              'select'=>["IS_CASH","ACTIVE"]
            ));


            if($arPaySystem = $rsPaySystem->fetch())
              if($arPaySystem["IS_CASH"] =="A"
                && $arPaySystem["ACTIVE"]=="Y"
                &&!$order->isShipped()
                &&!$order->isCanceled()
                &&!$order->isPaid()
                && in_array($order->getField("STATUS_ID"), self::getAllowPaymentStatuses() )
              )
              {
                 return true;
              }
          }
        }
      return false;
    }

  public static function getOrderCard(int $id,$user_id)
  {
    $result = new ResultData();
    $result->setStatusNotRelease();//@TODO: Убрать после реализации метода
    $data = [];
    if (!$data['error'] && !$data['error_message']) {
      $result->setData($data);
    } else {
      $result->setErrors($data['error'], $data['error_message']);
    }

    return $result;
  }

}