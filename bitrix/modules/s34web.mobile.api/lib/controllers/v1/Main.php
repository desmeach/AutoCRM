<?php

namespace s34web\Mobile\Api\controllers\v1;

use OpenApi\Annotations as OA;
use s34web\Mobile\Api\controllers\v1\classes\cErrors;
use s34web\Mobile\Api\controllers\v1\classes\cMain;
use s34web\Mobile\Api\controllers\v1\classes\cGeneral;
use s34web\Mobile\Api\controllers\v1\classes\ResultData;
use s34web\Mobile\Api\Response;

include(__DIR__ . "/classes/ResultData.php");
include(__DIR__ . "/classes/cGeneral.php");
include(__DIR__ . "/classes/cMain.php");
include(__DIR__ . "/classes/cErrors.php");


/**
 *
 * @OA\Servers(
 *     @OA\Server(
 *      url="https://site.ru",
 *      description="Тестовый сервер"
 *     ),
 *   ),
 * @OA\Info(
 *      title = "API для Mobile Mobile",
 *      version="1.0.4",
 *      contact= {
 *          "email"= "alex@34web.ru"
 *      },
 *  ),
 * @OA\SecurityScheme(
 *     type="apiKey",
 *     description="токен пользователя (jwt), Пример: eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzM4NCJ9.eyJ1c2VyX2lkIjoyNSwiYXV0aF9pZCI6MiwiZGV2aWNlX3Rva2VuIjoiYWFhYWFhYWFzZGYyM2ZmMjNmYTEyM3ZhcyIsImV4cCI6MTYxMDQ2MDE2MX0.blLpxY3Djnyphj72HnV6kEstzL4kTcCYHIyaVH9lSsDP05GYtIZ9GQMQQFakj2YD",
 *     name="X-Auth-Token",
 *     in="header",
 *     securityScheme="apiKeyAuth"
 * ),
 * @OA\Schema(
 *      required={"success"},
 *      schema="SuccessModel",
 *      @OA\Property(
 *             property="success",
 *             type="boolean",
 *             example="true",
 *     )
 * ),
 * @OA\Schema(
 *      required={"success","error","error_message"},
 *      schema="ErrorModel",
 *      @OA\Property(property="success", type="boolean", description="", example="false"),
 *      @OA\Property(property="error_message", type="array", @OA\Items(ref="#/components/schemas/ErrorObjectModel"), description="список ошибок"),
 * ),
 * @OA\Schema(schema="timestamp", type="integer", format="int64", minimum=1, description="unix timestamp UTC+0"),
 * @OA\Response(
 *      response="401",
 *      description="Срок действия токена истёк, требуется продление.",
 * ),
 * @OA\Response(
 *      response="404",
 *      description="Результаты не найдены",
 *     @OA\JsonContent(
 *      @OA\Property(property="success", type="boolean", description="", example="false"),
 *     )
 * ),
 * @OA\Response(
 *      response="500",
 *      description="Внутренная ошибка сервера",
 * ),
 * @OA\Schema(
 *      required={"text","code"},
 *      schema="ErrorObjectModel",
 *      @OA\Property(property="text", type="string",  description="текст ошибки", example="Нетипизированная ошибка"),
 *      @OA\Property(property="code", type="integer", format="int32", description="код ошибки", example="-1" ),
 * ),
 * @OA\Schema(
 *      required={"id","quantity"},
 *      schema="BasketItem",
 *      @OA\Property(property="id", type="integer", format="int32",  description="id товара", example=249787),
 *      @OA\Property(property="quantity", type="number", format="float",  description="количество товара", example=10),
 * ),
 * @OA\Schema(
 *      required={"id","isAvail","name","quantity","quantityNeed"},
 *      schema="BasketItemResult",
 *      @OA\Property(property="discount", type="integer",  description="скидка",example=2399),
 *      @OA\Property(property="id", type="integer",  description="id товара", example=249796),
 *      @OA\Property(property="isAvail", type="boolean", description="доступность товара к покупке", example="true"),
 *      @OA\Property(property="name", type="string", description="название товара", example="Doogee S60 (4GB, 64GB, Gold)"),
 *      @OA\Property(property="price", type="number",   format="float", description="цена со скидкой", example=21591),
 *      @OA\Property(property="priceBase", type="number",format="float",  description="базовая цена", example=23990),
 *      @OA\Property(property="quantity", type="integer",  description="количество", example=1),
 *      @OA\Property(property="quantityNeed", type="integer", description="требуемое количество", example=5),
 * ),
 * @OA\Schema(
 *      required={"id","isAvail","name","quantity","quantityNeed"},
 *      schema="BasketItemNotAvailResult",
 *      @OA\Property(property="errors", type="array", @OA\Items(anyOf={@OA\Schema(type="string", example="Доступное для покупки количество товара для покупки: 1")}), description="список ошибок"),
 *      @OA\Property(property="id", type="integer",  description="id товара", example=249796),
 *      @OA\Property(property="isAvail", type="boolean", description="доступность товара к покупке", example="true"),
 *      @OA\Property(property="name", type="string", description="название товара", example="Doogee S60 (4GB, 64GB, Gold)"),
 *      @OA\Property(property="quantity", type="integer",  description="количество", example=1),
 *      @OA\Property(property="quantityNeed", type="integer", description="требуемое количество", example=5),
 * ),
 * @OA\Schema(
 *      schema="BasketRequest",
 *      required={"trades","location_code"},
 *      @OA\Property(property="trades",  type="array", @OA\Items(ref="#/components/schemas/BasketItem"),  description="массим товаров для проверки наличия в базе магазина."),
 *      @OA\Property(property="location_code",  type="string",  description="код местоположения.", example="0000428505"),
 *      @OA\Property(property="delivery_id",  type="integer",  description="код типа доставки. Если не указать, будет получена первая по списку."),
 *      @OA\Property(property="payment_id",  type="integer",  description="код типа оплаты"),
 * )
 *
 * Class User
 *
 */

/**
 * Главная подсистема
 *
 * Версия модуля: 0.1.0
 *
 * Разработчик: студия 34web
 *
 * Поддержка: alex@34web.ru
 *
 * Список функций раздела
 * 1. получение информация о компании
 * 2. получение информации о доставке и оплате
 * 3. получение списка услуг
 * 4. получение карточки услуги
 * 5. список акций (название, путь к картинке, дата начала активности, дата конца активности, Краткое описание акции)
 * 6. карточка акции (название, картинка, путь к картинке, дата начала активности, дата конца активности, Полное описание акции)
 *
 * @package s34web\Mobile\Api\controllers\v1
 */
class Main
{
    // MAIN METHOD
    //https://manual.phpdoc.org/HTMLSmartyConverter/HandS/phpDocumentor/tutorial_tags.example.pkg.html

    /**
     * Метод для проверки состоятия подключения
     *
     * https://site.ru/api/v1/main/check/
     * @version 1.0
     */
    /**
     * @ OA\Get(
     *     path="/api/v1/main/check/",
     *     summary="Метод для проверки состоятия подключения",
     *     tags={"Главный модуль"},
     *     @ OA\Response(
     *     response="200",
     *     description="Возвращает параметры подключения",
     *    )
     * )
     */
    public function check()
    {
        $arResult = cGeneral::getRequest();
        Response::ShowResult($arResult);
    }


    public function checkClosed()
    {
        $arResult = cGeneral::getRequestPersonal();
        Response::ShowResult($arResult);
    }


    /**
     * Метод для получения информации о компании
     *
     * ```
     * @version 0.6
     */
    /**
     * @OA\Get(
     *     path="/api/v1/main/getCompanyInfo/",
     *     summary="Метод для получения информации о компании",
     *     tags={"Главный модуль"},
     *     @OA\Response(
     *      response="200",
     *      description="Возвращает информацию о компании",
     *      @OA\JsonContent(
     *         @OA\Property( property="text", type="string", example="Mobile - это розничная с…и и итоговому продукту."),
     *      ),
     *    ),
     *    @OA\Response(
     *          response="500",
     *          ref="#/components/responses/500"
     *     ),
     * )
     */
    public function getCompanyInfo()
    {
        $arParams = cGeneral::getRequest();
        $arResult = cMain::getCompanyInfo();

        cGeneral::sendResponse($arResult, $arParams);
    }

    /**
     *  Метод для получения информации о доставке и оплате
     *
     *  https://site.ru/api/v1/main/getDeliveryPaymentInfo/
     *
     *  Результат:
     *  ```
     *  {
     *   "success": true,
     *   "delivery": "При заказе товара в инте…а службой Boxberry.",
     *   "payment": "Способы оплаты покупки в…т-магазине stimul.tel."
     *  }
     *
     * @version 0.6
     */
    /**
     * @OA\Get(
     *     path="/api/v1/main/getDeliveryPaymentInfo/",
     *     summary="Метод для получения информации о доставке и оплате",
     *     tags={"Главный модуль"},
     *     @OA\Response(
     *      response="200",
     *      description="Возвращает информацию о доставке и оплате",
     *      @OA\JsonContent(
     *         @OA\Property( property="success", type="boolean", example="true"  ),
     *         @OA\Property( property="delivery", type="string", example="При заказе товара в инте…а службой Boxberry."),
     *         @OA\Property( property="payment", type="string", example="Способы оплаты покупки в…т-магазине stimul.tel."),
     *      ),
     *    ),
     *    @OA\Response(
     *          response="500",
     *          ref="#/components/responses/500"
     *     ),
     *  ),
     */
    public function getDeliveryPaymentInfo()
    {
        $arParams = cGeneral::getRequest();
        $arResult = new ResultData();
        $arResultDelivery = cMain::getDeliveryInfo();
        $arResultPayment = cMain::getPaymentInfo();

        if ($arResultDelivery->isSuccess() && $arResultPayment->isSuccess()) {
            $arResult->setParam("delivery", $arResultDelivery->getData()["text"]);
            $arResult->setParam("payment", $arResultPayment->getData()["text"]);
        } else {
            $arResult->setErrors(cErrors::NO_DATA);
        }
        cGeneral::sendResponse($arResult, $arParams);
    }

}