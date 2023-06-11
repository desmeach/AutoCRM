<?php

namespace s34web\Mobile\Api\controllers\v1;

use OpenApi\Annotations as OA;
use s34web\Mobile\Api\controllers\v1\classes\cCabinet;
use s34web\Mobile\Api\controllers\v1\classes\cErrors;
use s34web\Mobile\Api\controllers\v1\classes\cLocations;
use s34web\Mobile\Api\controllers\v1\classes\cGeneral;
use s34web\Mobile\Api\controllers\v1\classes\ResultData;

include(__DIR__ . "/classes/ResultData.php");
include(__DIR__ . "/classes/cGeneral.php");
include(__DIR__ . "/classes/cLocations.php");

/**
 * Подсистема Личный кабинет
 *
 * Версия модуля: 0.5.5
 *
 * Разработчик: студия 34web
 *
 * Поддержка: alex@34web.ru
 *
 * Список функций раздела
 * 1. Получение местоположений по фильтру 
 * 2. Изменение данных профиля (фамилия, имя, отчество, телефон, дата рождения (после ввода не меняется))
 * 3. Список заказов (Номер заказа, сумма заказа, дата создания, статус оплаты, статус заказа, тип доставки, адрес магазина для самовывоза, адрес покупателя для заказа курьером, список товаров с иконками). Сортировка заказов по дате добавления.
 * 4. Карточка заказа (слайдер по товарам, картинка (до 500 пикселей по самой узкой стороне. соотношение, примерное - высота = 1.20 * ширины), название с торговыми предложениями и цены)
 *
 * @package s34web\Mobile\Api\controllers\v1
 */
class Cabinet
{
    // MAIN METHOD
    //https://manual.phpdoc.org/HTMLSmartyConverter/HandS/phpDocumentor/tutorial_tags.example.pkg.html
    //const IS_TEST_MODE = true;

    /**
     * 1. Метод получения списка местоположений.
     *
     * @version 1.0
     */
    /**
     * @OA\Get(
     *     path="/api/v1/cabinet/getLocations/",
     *     summary="Метод получения списка местоположений, с возможностью поиска по подстроке. Доступны Волжский и Волгоград",
     *     tags={"Личный кабинет"},
     *     @OA\Parameter (
     *          name="query",
     *          in="query",
     *          required=true,
     *          description="строка, с которой начинается город. Длина параметра не менее 3 символов.",
     *          example="Волж",
     *          @OA\Schema(type="string"),
     *     ),
     *     @OA\Response(
     *          response="200",
     *          description="Возвращает информацию о списке местоположений",
     *          @OA\JsonContent(
     *               type="object",
     *               @OA\Property(property="ID", type="string",minLength=10,maxLength=10, description="код местоположения в базе", example="0000428505"),
     *               @OA\Property(property="CITY", type="string", description="название города", example="Волжский"),
     *               @OA\Property(property="FULL_NAME", type="string", description="полная адресная строка города", example="Россия, Юг, Волгоградская область, Волжский"),
     *          )
     *     ),
     *     @OA\Response(
     *           response="400",
     *           description="Ошибка получения списка местоположений, будет доступен список ошибок.

          Список кодов ошибок:
          -1 - Нетипизированная ошибка, смотрите текст сообщения
          5 - Не передан токен пользователя
          50 - Минимальная длина текста 3 символа
          111 - Ошибка структуры токена
    ",
     *        @OA\JsonContent(ref="#/components/schemas/ErrorModel")
     *      ),
     *     @OA\Response(
     *           response="404",
     *           ref="#/components/responses/404"
     *      ),
     *     @OA\Response(
     *          response="500",
     *          ref="#/components/responses/500"
     *     )
     * )
     */
    public function getLocations()
    {
        $arParams = cGeneral::getRequest(
            ["query" => "string_all_filter"]
        );
        if ($arParams !== false) {
            $query = $arParams["parameters"]["query"];
            $arResult = cLocations::getLocations($query);
            cGeneral::sendResponse($arResult);
        }
    }

    /**
     * 2. Метод для обработки данных профиля пользователя
     *
     * **Закрытый метод**
     *
     * Получение и обновление данных пользователя: фамилия, имя, отчество, телефон, дата рождения.
     * Дата рождения после установки не меняется через интерфейс.
     *
     * GET - получение данных текущего пользователя
     *
     * https://site.ru/api/v1/cabinet/profileData/?token=eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzM4NCJ9.eyJ1c2VyX2lkIjoyNSwiYXV0aF9pZCI6MiwiZGV2aWNlX3Rva2VuIjoiYWFhYWFhYWFzZGYyM2ZmMjNmYTEyM3ZhcyIsImV4cCI6MTYxMDQ2MDE2MX0.blLpxY3Djnyphj72HnV6kEstzL4kTcCYHIyaVH9lSsDP05GYtIZ9GQMQQFakj2YD
     *
     * PUT - сохранение данных текущего пользователя
     *
     * https://site.ru/api/v1/cabinet/profileData/?_method=PUT&second_name=test&name=test&phone=+79991234567&birthday=19990525&token=eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzM4NCJ9.eyJ1c2VyX2lkIjoyNSwiYXV0aF9pZCI6MiwiZGV2aWNlX3Rva2VuIjoiYWFhYWFhYWFzZGYyM2ZmMjNmYTEyM3ZhcyIsImV4cCI6MTYxMDQ2MDE2MX0.blLpxY3Djnyphj72HnV6kEstzL4kTcCYHIyaVH9lSsDP05GYtIZ9GQMQQFakj2YD
     *
     *
     * Параметры в заголовке запроса header
     * ```
     * X-AUTH-TOKEN (token) - токен пользователя (jwt).
     * ```
     *
     * Для получения данных методом GET
     *
     * Входные параметры:
     * ```
     * без параметров
     * ```
     *
     * Выходные параметры:
     * ```
     * {
     *  "last_name": "фамилия",
     *  "name": "имя",
     *  "second_name": "отчество",
     *  "birthday": 19990525,
     *  "phone": "+79991234567",
     *  "location": {
     *    "id": "0000428505", - код местоположения в базе (string)
     *    "city": "Волжский", - название города (string)
     *    "full_name": "Россия, Юг, Волгоградская область, Волжский" - полное название (string)
     *  }
     * }
     * ```
     *
     * Для обновления данных методом PUT
     *
     * Поля могут приходить отдельно по факту заполнения
     *
     * Входные параметры:
     * ```
     * {
     *  "name": "имя",
     *  "last_name": "фамилия",
     *  "second_name": "отчество",
     *  "birthday": 19990525,
     *  "phone": "+79991234567",
     *  "location_id": "0000428505", - код местоположения в базе, полученны при работе метода getLocations (string)
     * }
     * ```
     *
     * Выходные параметры:
     * ```
     * {
     *  "success"=>true
     * }
     * ```
     *
     * @version 1.0
     */
    /**
     * @OA\Get(
     *     path="/api/v1/cabinet/profileData/",
     *     summary="Метод получения данных текущего пользователя",
     *     tags={"Личный кабинет"},
     *     security={{"apiKeyAuth": {  "read:profile"   }}},
     *     @OA\Response(
     *      response="200",
     *      description="Возвращает информацию о пользователе.",
     *      @OA\JsonContent(
     *              type="object",
     *              @OA\Property( property="name", type="string", description="имя", example="имя"),
     *              @OA\Property( property="last_name", type="string", description="фамилия", example="фамилия"),
     *              @OA\Property( property="second_name", type="string",description="отчество", example="отчество"),
     *              @OA\Property( property="birthday", type="string",nullable=true, description="день рождения", example="19990525"),
     *              @OA\Property( property="phone", type="string",description="телефон", example="+79991234567"),
     *              @OA\Property( property="location",
     *                      type="object",
     *                      description="параметры",
     *                      @OA\Property( property="id", type="string",description="код местоположения в базе", example="0000428505"),
     *                      @OA\Property( property="city", type="string",description="название города", example="Волжский"),
     *                      @OA\Property( property="full_name", type="string", description="полное название", example="Россия, Юг, Волгоградская область, Волжский"),
     *              )
     *           )
     *     ),
     *     @OA\Response(
     *      response="400",
     *      description="Информация об ошибках, будет доступен список ошибок.

    Список кодов ошибок:
    -1 - Нетипизированная ошибка, смотрите текст сообщения
    5 - Не передан токен пользователя
    111 - Ошибка структуры токена
    ",
     *          @OA\JsonContent(ref="#/components/schemas/ErrorModel")
     *     ),
     *     @OA\Response(
     *          response="404",
     *          ref="#/components/responses/404"
     *     ),
     *     @OA\Response(
     *          response="500",
     *          ref="#/components/responses/500"
     *     )
     * ),
     * @OA\Put(
     *     path="/api/v1/cabinet/profileData/",
     *     summary="Метод для обновления данных профиля пользователя",
     *     tags={"Личный кабинет"},
     *     security={{"apiKeyAuth": {"write:profile"}}},
     *     @OA\RequestBody(
     *      @OA\MediaType(
     *              mediaType="multipart/form-data",
     *              @OA\Schema(
     *                 required={},
     *                 @OA\Property(property="name", type="string", description="имя, обязательное одно из трёх: second_name,name,last_name",example=""),
     *                 @OA\Property(property="last_name", type="string", description="отчество, обязательное одно из трёх: second_name,name,last_name",example=""),
     *                 @OA\Property(property="second_name", type="string", description="фамилия, обязательное одно из трёх: name,last_name,second_name",example=""),
     *                 @OA\Property(property="phone", type="string",  description="телефон, пример 79991234567",example=""),
     *                 @OA\Property(property="birthday", @OA\Schema(ref="#/components/schemas/timestamp"),  description="дата рождения, если установлена, изменить нельзя через API.",example=""),
     *                 @OA\Property(property="location_id", type="string", description="код местоположения, пример: 0000428505.",example=""),
     *              ),
     *      ),
     *      @OA\JsonContent(
     *        required={},
     *                 @OA\Property(property="name", type="string", description="имя, обязательное одно из трёх: second_name,name,last_name",example=""),
     *                 @OA\Property(property="last_name", type="string", description="отчество, обязательное одно из трёх: second_name,name,last_name",example=""),
     *                 @OA\Property(property="second_name", type="string", description="фамилия, обязательное одно из трёх: name,last_name,second_name",example=""),
     *                 @OA\Property(property="phone", type="string",  description="телефон, пример  79991234567",example=""),
     *                 @OA\Property(property="birthday", @OA\Schema(ref="#/components/schemas/timestamp"),  description="дата рождения, если установлена, изменить нельзя через API.",example=""),
     *                 @OA\Property(property="location_id", type="string", description="код местоположения, пример: 0000428505.",example=""),
     *      ),
     *     ),
     *     @OA\Response(
     *           response="200",
     *           description="Профиль успешно обновлён",
     *           @OA\JsonContent(ref="#/components/schemas/SuccessModel")
     *     ),
     *     @OA\Response(
     *           response="400",
     *           description="Обновление данных профиля завершилось с ошибками, будет доступен список ошибок.

            Список кодов ошибок:
            -1 - Нетипизированная ошибка, смотрите текст сообщения
            5 - Не передан токен пользователя
            12 - Телефон задан некорректно. Допустимый формат - 79999999999
            58 - Дата рождения пользователя была установлена ранее. Обратитесь в службу поддержки для её изменения
            59 - Дата передана в неверном формате. Поддерживаемый формат: YYYYMMDD
            60 - Обязательное одно из трёх: second_name, name, last_name
            111 - Ошибка структуры токена
    ",
     *           @OA\JsonContent(ref="#/components/schemas/ErrorModel")
     *     ),
     *     @OA\Response(
     *           response="404",
     *           ref="#/components/responses/404"
     *      ),
     *     @OA\Response(
     *          response="500",
     *          ref="#/components/responses/500"
     *     )
     * )
     */
    public function profileData()
    {
        $arRequest = cGeneral::getRequestPersonal();

        if (cGeneral::checkMethod($arRequest, "GET")) {
            //Получение данных о пользователе
            $user_id = $arRequest["user_id"];
            $arResult = cCabinet::getProfileData($user_id);
            cGeneral::sendResponse($arResult, $arRequest);
        } elseif (cGeneral::checkMethod($arRequest, "PUT")) {
            //Обновление данных
            $user_id = $arRequest["user_id"];

            $second_name = $arRequest["parameters"]["second_name"];

            $name = $arRequest["parameters"]["name"];
            $last_name = $arRequest["parameters"]["last_name"];
            $phone = $arRequest["parameters"]["phone"];
            $birthday = $arRequest["parameters"]["birthday"];
            $location_id = $arRequest["parameters"]["location_id"];
            $arResult = cCabinet::setProfileData(
                $user_id,
                $second_name,
                $name,
                $last_name,
                $phone,
                $birthday,
                $location_id
            );
            cGeneral::sendResponse($arResult, $arRequest);
        } else {
            $arTmp = new ResultData();
            $arTmp->setErrors(cErrors::INCORRECT_METHOD);
            cGeneral::sendResponse($arTmp);
        }
    }

    /**
     * 3. Метод для получения списка актуальных заказов пользователя (не в статусе F)
     *
     * **Закрытый метод**
     *
     * Сортировка заказов по дате добавления.
     *
     * Вывод производится постранично!
     *
     * Параметры в заголовке запроса header
     * ```
     * X-AUTH-TOKEN (token) - токен пользователя (jwt).
     * ```
     *
     * https://site.ru/api/v1/cabinet/getOrderList/?token=eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzM4NCJ9.eyJ1c2VyX2lkIjoyNSwiYXV0aF9pZCI6MiwiZGV2aWNlX3Rva2VuIjoiYWFhYWFhYWFzZGYyM2ZmMjNmYTEyM3ZhcyIsImV4cCI6MTYxMDQ2MDE2MX0.blLpxY3Djnyphj72HnV6kEstzL4kTcCYHIyaVH9lSsDP05GYtIZ9GQMQQFakj2YD
     *
     * Входные параметры:
     * ```
     * page(int) - номер страницы, без указания параметра будет первая страница
     * ```
     *
     * Выходные параметры:
     * ```
     * [
     *  {
     *    "id": 83,                                                      -id заказа (int)
     *    "create_date": 1610713616,                                     -дата создания заказа (timestamp UTC+0)
     *    "update_date": 1611055777,                                     -время обновления заказа (timestamp UTC+0)
     *    "total_price": 72980,                                          -сумма заказа (float)
     *    "discount_price": 0,                                           -сумма скидки (float)
     *    "delivery_price": 0,                                           -стоимость доставки (float)
     *    "is_paid": true,                                               -статус оплаты (true/false)
     *    "is_online_payment": true,                                     -флаг доступности оплаты (true/false)
     *    "is_canceled": false,                                          -статус отмены (true/false)
     *    "order_number": "83",                                          -номер заказа (string)
     *    "status": {                                                    -статус заказа
     *       "id": "D",                                                  -код статуса заказа
     *       "name": "Отменен"
     *     },
     *    "pay_system": {                                                -система оплаты
     *       "id": 1,
     *       "name":"При получении"
     *     },
     *    "delivery": {                                                 -способ доставки
     *       "id": 2,
     *       "name": "Самовывоз (онлайн оплата)"
     *       "store_id": 38,                                            -id cклада самовывоза(int)
     *       "address": "Салон связи \"PRO Связь\" _                    -адрес склада самовывоза
     *                    (г. Волжский, Бульвар Профсоюзов, 1 б, _
     *                      ТРК \"ПланетаЛето\", 1 этаж, центр.вход)"
     *       "is_pickup": true                                          -самовывоз из пункта выдачи
     *
     *     },
     *     или
     *    "delivery": {                                                 -способ доставки
     *       "id": 2,
     *       "name": "Бесплатная доставка курьером г. Волжский",
     *       "address": "109383, Москва г, Батюнинский проезд, ..."
     *       "is_pickup": false                                         -доставка до адреса
     *     },
     *    "products": {                                                 -список товаров
     *       {
     *          "id": 249693,                                           -id товара (int)
     *          "name": "Nobby S300 Pro",                               -наименование товара
     *          "parameters": "1GB, 8GB, Black",                        -параметры товара
     *          "price": 3890,                                          -цена за единицу товара (float)
     *          "quantity": 1,                                          -количество в заказе (float)
     *          "total_price": 3890,                                    -итоговая сумма (float)
     *          "parent_product_id": 249692,                            -id основного товара в каталоге (int)
     *          "in_stock": true,                                       -флаг доступности в каталоге (true/false)
     *          "picture_url": "https://site.ru/...a6ba.png"
     *       },
     *       ...
     *    }
     *    ...
     *  },
     *  ...
     * ]
     * ```
     *
     * @version 0.5
     */
    /**
     * @OA\Get(
     *     path="/api/v1/cabinet/getOrderList/",
     *     summary="Метод для получения списка актуальных заказов пользователя (не в статусе F)",
     *     description= "Сортировка заказов по дате добавления.<br>
    Выдача списка актуальных заказов производится постранично, количество на странице - 10.<br>
    Кэш списка заказов на 10 минут.
    ",
     *     security={{"apiKeyAuth": {  "read:profile"   }}},
     *     tags={"Личный кабинет"},
     *     @OA\Parameter (
     *          name="page",
     *          in="query",
     *          required=false,
     *          description="номер страницы, без указания параметра будет первая страница",
     *          @OA\Schema(type="integer",default=1,minimum=1),
     *     ),
     *     @OA\Response(
     *      response="200",
     *      description="Возвращает информацию о списке заказов.",
     *      @OA\JsonContent(
     *           type="array",
     *           @OA\Items(
     *              type="object",
     *              @OA\Property( property="id",                type="integer", description="id заказа", example=83),
     *              @OA\Property( property="total_price",       type="number", description="сумма заказа", example=72980),
     *              @OA\Property( property="discount_price",    type="number", description="сумма скидки", example=0),
     *              @OA\Property( property="delivery_price",    type="number", description="стоимость доставки", example=0),
     *              @OA\Property( property="is_paid",           type="boolean", description="статус оплаты", example=true),
     *              @OA\Property( property="is_online_payment", type="boolean",description="флаг доступности оплаты", example=true),
     *              @OA\Property( property="is_canceled",       type="boolean",description="статус отмены", example=false),
     *              @OA\Property( property="order_number",      type="string", description="номер заказа", example=83),
     *              @OA\Property( property="create_date",       @OA\Schema(ref="#/components/schemas/timestamp"), description="дата и время создания заказа", example="1610713616"),
     *              @OA\Property( property="update_date",       @OA\Schema(ref="#/components/schemas/timestamp"), description="дата и время обновления заказа", example="1611055777"),
     *              @OA\Property( property="status",            type="object", description="статус заказа", example={"id":"N","name":"Оформлен"},
     *                      @OA\Property(property="id",         type="string",  description="код статуса", example="N"),
     *                      @OA\Property(property="name",       type="string",  description="название статуса", example="Оформлен"),
     *              ),
     *              @OA\Property( property="pay_system",        type="object",  description="способ оплаты", example={"id":1,"name":"При получении"},
     *                      @OA\Property(property="id",         type="integer", description="id оплаты", example=1),
     *                      @OA\Property(property="name",       type="string",  description="Название платёжной системы", example="При получении"),
     *              ),
     *              @OA\Property( property="delivery",          type="object",   description="способ доставки", example={"id":1,"name":"самовывоз (онлайн оплата)"},
     *                      @OA\Property(property="id",         type="integer", description="id оплаты", example=2),
     *                      @OA\Property(property="name",       type="string",  description="Название способа доставки", example="самовывоз (онлайн оплата)"),
     *                      @OA\Property(property="store_id",   type="integer",  description="id склада", example=38),
     *                      @OA\Property(property="address",    type="string",  description="адрес пункта самовывоза", example="Салон связи ""PRO Связь"" (г. Волжский, Бульвар Профсоюзов, 1 б, ТРК ""ПланетаЛето"", 1 этаж, центр.вход)"),
     *                      @OA\Property(property="is_pickup",  type="boolean",  description="самовывоз из пункта выдачи", example=true),
     *              ),
     *              @OA\Property(
     *                  property="products",
     *                  type="array",
     *                  @OA\Items(
     *                      type="object",
     *                      @OA\Property( property="id",                 type="integer", description="id товара", example=249693),
     *                      @OA\Property( property="name",               type="string", description="наименование товара", example="Nobby S300 Pro"),
     *                      @OA\Property( property="parameters",         type="string", description="параметры торгового предложения", example="1GB, 8GB, Black"),
     *                      @OA\Property( property="price",              type="number", description="цена за единицу товара", example=3890),
     *                      @OA\Property( property="quantity",           type="integer", description="количество в заказе", example=1),
     *                      @OA\Property( property="total_price",        type="number", description="итоговая сумма", example=3890),
     *                      @OA\Property( property="in_stock",           type="boolean", description="флаг доступности в каталоге", example="true"),
     *                      @OA\Property( property="parent_product_id",  type="integer", description="id основного товара в каталоге", example=249692),
     *                      @OA\Property( property="picture_url",        type="string", description="ссылка на изображение", example="https://stimul.tel/upload/iblock/929/prmvs0qmjiovrqdg9os0sia3q6qs781d/86f5550ade0511ea80d32cfda134a6ba_ae26d37ca44e11ec80dc2cfda134a6ba.png"),
     *                  )
     *              )
     *        )
     *      )
     *      ),
     *     @OA\Response(
     *      response="400",
     *      description="Информация об ошибках, будет доступен список ошибок.

            Список кодов ошибок:
            -1 - Нетипизированная ошибка, смотрите текст сообщения
            5 - Не передан токен пользователя
            49 - Некорректно заполнены обязательные параметры: [список полей запроса]
            111 - Ошибка структуры токена
     ",
     *          @OA\JsonContent(ref="#/components/schemas/ErrorModel")
     *     ),
     *     @OA\Response(
     *          response="500",
     *          ref="#/components/responses/500"
     *     ),
     * ),
     */
    public function getOrderList()
    {
        $arRequest = cGeneral::getRequestPersonal();
        if (cGeneral::checkMethodRequire($arRequest, "GET")
            || cGeneral::checkMethodRequire($arRequest, "POST")) {
            $user_id = $arRequest["user_id"];
            $page = (int)$arRequest["parameters"]["page"];
            $page_count = 10;

            $arResult = cCabinet::getOrderList($user_id, $page, $page_count);
            cGeneral::sendResponse($arResult);
        }
    }

    /**
     * 5. Метод получения фиксированного списка местоположений.
     *
     * https://site.ru/api/v1/cabinet/getLocationsFixed/
     *
     * Входные параметры:
     * ```
     * Без параметров
     * ```
     *
     * Выходные параметры:
     * ```
     * {
     *      "ID": "0000428505", - код местоположения в базе (string)
     *      "CITY": "Волжский", - название города (string)
     * },
     * {
     *      "ID": "0000426112", - код местоположения в базе (string)
     *      "CITY": "Волгоград", - название города (string)
     * },
     * ```
     *
     * @version 1.0
     */
    /**
     * @OA\Get(
     *     path="/api/v1/cabinet/getLocationsFixed/",
     *     summary="Метод получения фиксированного списка местоположений",
     *     tags={"Личный кабинет"},
     *     @OA\Response(
     *      response="200",
     *      description="Возвращает информацию о фиксированном списке местоположений.",
     *      @OA\JsonContent(
     *           type="array",
     *           @OA\Items(
     *              type="object",
     *              @OA\Property( property="ID", type="string", description="код местоположения в базе", example="0000428505"),
     *              @OA\Property( property="CITY", type="string", description="название города", example="Волжский"),
     *           ),
     *      )
     *    ),
     * @OA\Response(
     *          response="500",
     *          ref="#/components/responses/500"
     *     ),
     * )
     */
    public function getLocationsFixed()
    {
        $arParams = cGeneral::getRequest();
        if ($arParams !== false) {
            $arResult = cLocations::getLocationsFixed();
            cGeneral::sendResponse($arResult);
        }
    }

}