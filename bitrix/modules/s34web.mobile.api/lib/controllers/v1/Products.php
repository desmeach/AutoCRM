<?php
namespace s34web\Mobile\Api\controllers\v1;

use s34web\Mobile\Api\controllers\v1\classes\cCatalog;
use s34web\Mobile\Api\controllers\v1\classes\cErrors;
use s34web\Mobile\Api\controllers\v1\classes\cGeneral;
use s34web\Mobile\Api\controllers\v1\classes\cProducts;
use s34web\Mobile\Api\controllers\v1\classes\ResultData;

include(__DIR__ . "/classes/ResultData.php");
include(__DIR__ . "/classes/cGeneral.php");
include(__DIR__ . "/classes/cErrors.php");
include(__DIR__ . "/classes/cCatalog.php");

/**
 * Подсистема Каталог
 *
 * Версия модуля: 0.7.8
 *
 * Разработчик: студия 34web
 *
 * Поддержка: alex@34web.ru
 *
 * @package s34web\Mobile\Api\controllers\v1
 */
class Products
{
    //https://manual.phpdoc.org/HTMLSmartyConverter/HandS/phpDocumentor/tutorial_tags.example.pkg.html
    const IS_TEST_MODE = true;

    // CATALOG methods

    /**
     * Метод получения списка категорий каталога для главной.
     *
     * @version 1.0
     */
    /**
     * @OA\Get(
     *     path="/api/v1/catalog/getCategoriesMain/",
     *     summary="Метод получения списка категорий каталога для главной.",
     *     tags={"Каталог"},
     *     @OA\Response(
     *      response="200",
     *      description="Возвращает информацию о списке категорий",
     *      @OA\JsonContent(
     *         type="array",
     *         @OA\Items(type="object",
     *              @OA\Property( property="id", type="integer", description="id товара", example=664),
     *              @OA\Property( property="name", type="string", description="название товара", example="Подарочные сертификаты"),
     *              @OA\Property( property="icon", type="string", description="иконка для раздела", example="https://stimul.tel/upload/iblock/91e/Smartfony.png"),
     *         ),
     *      ),
     *     ),
     *
     *    @OA\Response(
     *          response="500",
     *          ref="#/components/responses/500"
     *     ),
     *  ),
     * )
     *
     */
    public function getProducts()
    {
        //$arRequest = $this->getRequest();
        $arResult = cProducts::getProducts();
        cGeneral::sendResponse($arResult);
    }

    /**
     * Метод получения дерева категорий для каталога.
     * Внешний вид отображения раздела можно определить по наличию хотябы одной картинки
     *
     *
     * @version 1.0
     */
    /**
     * @OA\Schema(
     *       schema="CategoryModelLevel2",
     *       required={"id","name","main_category_id"},
     *       @OA\Property( property="id", type="integer", description="id категории", example=962),
     *       @OA\Property( property="name", type="string", description="название категории", example="Apple iPhone"),
     *       @OA\Property( property="main_category_id", type="integer", description="id категории 1 уровня", example=664),
     *       @OA\Property( property="icon", type="string", description="иконка для категории", example="https://stimul.tel/upload/iblock/911/Apple-600x600.png"),
     *       @OA\Property( property="child", type="array", description="массив со вложенными разделами", @OA\Items(), example={}),
     * ),
     * @OA\Schema(
     *       schema="CategoryModel",
     *       required={"id","name"},
     *       @OA\Property( property="id", type="integer", description="id категории", example=664),
     *       @OA\Property( property="name", type="string", description="название категории", example="Смартфоны"),
     *       @OA\Property( property="icon", type="string", description="иконка для категории", example="https://stimul.tel/upload/iblock/91e/Smartfony.png"),
     *       @OA\Property( property="child", type="array", description="массив со вложенными разделами", @OA\Items(ref="#/components/schemas/CategoryModelLevel2")),
     * ),
     * @OA\Get(
     *     path="/api/v1/catalog/getCategoriesTree/",
     *     summary="Метод получения дерева категорий для каталога.",
     *     description="Внешний вид отображения раздела можно определить по наличию хотябы одной картинки.",
     *     tags={"Каталог"},
     *     @OA\Response(
     *      response="200",
     *      description="Возвращает информацию о дереве категорий для каталога",
     *      @OA\JsonContent(
     *         type="array",
     *         @OA\Items(ref="#/components/schemas/CategoryModel"),
     *      ),
     *     ),
     *    @OA\Response(
     *          response="500",
     *          ref="#/components/responses/500"
     *     ),
     *  ),
     * )
     */
    public function getBranchProducts()
    {
        //$arParams = General::getRequest();
        $arRequest = cGeneral::getRequest();
        if (cGeneral::checkMethod($arRequest, "GET")) {
            $branch_id = $arRequest["parameters"]["branch_id"];
            $arResult = cProducts::getBranchProducts($branch_id);
            cGeneral::sendResponse($arResult, $arRequest);
        } else {
            $arTmp = new ResultData();
            $arTmp->setErrors(cErrors::INCORRECT_METHOD);
            cGeneral::sendResponse($arTmp);
        }
    }

    /**
     * Метод получения списка свойств каталога
     *
     * Свойства могут быть как для основного товара, так и для торгового предложения.
     * Названия свойств могут совпадать, однако id отличаются
     *
     * https://site.ru/api/v1/catalog/getCatalogProps/
     *
     * Входные параметры:
     *
     * ```
     * без параметров
     * ```
     *
     * Выходные данные:
     * ```
     * {
     *  "id": 3031,
     *  "code": "PLOTNOST_STEKLA", - код свойства (string)
     *  "name": "Плотность стекла", - название свойства (string)
     *  "type": "L",  - допустимые типы свойства: L - список, N - число, S - строка, LP - список с картинками
     *  "group_name": "Коммуникации", - название группы св-в (string)
     *  "group_sort": "80", - сортировка для группы (int)
     *  "filter_show": true, - отображать в фильтре true/false
     *  "filter_section_id": 0, - id категории каталога для отображения фильтра
     *  "values": [  - список возможных значений для фильтра id=значение
     *    {
     *      "id": 583340, - id значения свойства
     *      "value": "5 МP" - значение св-ва
     *    },
     *    ...
     *  ]
     *  ...
     * },
     * {
     *  "id": 2897,
     *  "code": "TEKHNOLOGIYA_2G",
     *  "name": "Технология 2G",
     *  "type": "S",
     *  "filter_show": false
     *  },
     *   {
     *   "id": 2793,
     *   "code": "KOLICHESTVO_YADER",
     *   "name": "Количество ядер",
     *   "type": "N",
     *   "filter_show": true,
     *   "filter_section_id": 704
     *   },
     * {
     *   "id": 2932,
     *   "code": "BRAND",
     *   "name": "Бренд",
     *  "type": "LP",
     *   "filter_show": true,
     *   "filter_section_id": 0,
     *   "values": [
     *       {
     *      "id": 99,
     *      "value": "Apple",
     *      "picture_url": "https://site.ru/upload/iblock/ec7/apple.png"
     *      },
     *   ]
     * }
     * ```
     * @version 1.0
     */
    /**
     * @OA\Schema(
     *       schema="PropItemLValueModel",
     *       required={"id","value"},
     *       @OA\Property( property="id", type="integer", description="код значения", example=583340),
     *       @OA\Property( property="value", type="string", description="значение", example="5 МP"),
     * ),
     * @OA\Schema(
     *       schema="PropItemLPValueModel",
     *       required={"id","value"},
     *       @OA\Property( property="id", type="integer", description="код значения", example=99),
     *       @OA\Property( property="value", type="string", description="значение", example="Apple"),
     *       @OA\Property( property="picture_url", type="string", description="ссылка на картинку", example="https://stimul.tel/upload/iblock/ec7/apple.png"),
     * ),
     * @OA\Schema(
     *       schema="PropItemNModel",
     *       required={"id","name","code","type"},
     *       @OA\Property( property="id", type="integer", description="id свойства", example=2793),
     *       @OA\Property( property="code", type="string", description="код св-ва", example="KOLICHESTVO_YADER"),
     *       @OA\Property( property="name", type="string", description="название свойства", example="Количество ядер"),
     *       @OA\Property( property="type", type="string", example="N", description="тип для отображения в фильтре - число"),
     *       @OA\Property( property="group_name", type="string", description="название группы св-в", example="Железо"),
     *       @OA\Property( property="group_sort", type="integer", description="сортировка для группы", example=80),
     *       @OA\Property( property="filter_show", type="boolean", description="отображать в фильтре", example="true"),
     *       @OA\Property( property="filter_section_id", type="integer", description="раздел для фильтра, 0 - отображается во всех разделах", example=704),
     * ),
     * @OA\Schema(
     *       schema="PropItemSModel",
     *       required={"id","name","code","type"},
     *       @OA\Property( property="id", type="integer", description="id свойства", example=2897),
     *       @OA\Property( property="code", type="string", description="код св-ва", example="TEKHNOLOGIYA_2G"),
     *       @OA\Property( property="name", type="string", description="название свойства", example="Плотность стекла"),
     *       @OA\Property( property="type", type="string", example="S",  description="тип для отображения в фильтре - сторока"),
     *       @OA\Property( property="group_name", type="string", description="название группы св-в", example="Коммуникации"),
     *       @OA\Property( property="group_sort", type="integer", description="сортировка для группы", example=100),
     *       @OA\Property( property="filter_show", type="boolean", description="отображать в фильтре", example="true"),
     *       @OA\Property( property="filter_section_id", type="integer", description="раздел для фильтра, 0 - отображается во всех разделах", example=0),
     * ),
     * @OA\Schema(
     *       schema="PropItemLModel",
     *       required={"id","name","code","type"},
     *       @OA\Property( property="id", type="integer", description="id свойства", example=3031),
     *       @OA\Property( property="code", type="string", description="код св-ва", example="PLOTNOST_STEKLA"),
     *       @OA\Property( property="name", type="string", description="название свойства", example="Плотность стекла"),
     *       @OA\Property( property="type", type="string", example="L", description="тип для отображения в фильтре - список"),
     *       @OA\Property( property="group_name", type="string", description="название группы св-в", example="Дисплей"),
     *       @OA\Property( property="group_sort", type="integer", description="сортировка для группы", example=8),
     *       @OA\Property( property="filter_show", type="boolean", description="отображать в фильтре", example="true"),
     *       @OA\Property( property="filter_section_id", type="integer", description="раздел для фильтра, 0 - отображается во всех разделах", example=0),
     *       @OA\Property( property="values", type="array", description="массив со списком доступных значений", @OA\Items(ref="#/components/schemas/PropItemLValueModel")),
     * ),
     * @OA\Schema(
     *       schema="PropItemLPModel",
     *       required={"id","name"},
     *       @OA\Property( property="id", type="integer", description="id свойства", example=664),
     *       @OA\Property( property="code", type="string", description="код св-ва", example="BRAND"),
     *       @OA\Property( property="name", type="string", description="название свойства", example="Бренд"),
     *       @OA\Property( property="type", type="string", example="LP",  description="тип для отображения в фильтре - сторока с картинками"),
     *       @OA\Property( property="group_name", type="string", description="название группы св-в", example="Основные"),
     *       @OA\Property( property="group_sort", type="integer", description="сортировка для группы", example=120),
     *       @OA\Property( property="filter_show", type="boolean", description="отображать в фильтре", example="true"),
     *       @OA\Property( property="filter_section_id", type="integer", description="раздел для фильтра, 0 - отображается во всех разделах", example=0),
     *       @OA\Property( property="values", type="array", description="массив со списком доступных значений", @OA\Items(ref="#/components/schemas/PropItemLPValueModel")),
     * ),
     * @OA\Get(
     *     path="/api/v1/catalog/getCatalogProps/",
     *     summary="Метод получения списка всех свойств каталога",
     *     description="Свойства могут быть как для основного товара, так и для торгового предложения. Названия свойств могут совпадать, однако id отличаются.
    Допустимые типы свойств: L - список, N - число, S - строка, LP - список с картинками",
     *     tags={"Каталог"},
     *     @OA\Response(
     *      response="200",
     *      description="Возвращает информацию о списке св-в каталога",
     *      @OA\JsonContent(
     *         type="array",
     *         @OA\Items(oneOf={
     *              @OA\Schema(ref="#/components/schemas/PropItemNModel"),
     *              @OA\Schema(ref="#/components/schemas/PropItemSModel"),
     *              @OA\Schema(ref="#/components/schemas/PropItemLModel"),
     *              @OA\Schema(ref="#/components/schemas/PropItemLPModel"),
     *          }),
     *      ),
     *     ),
     * )
     */
    public function getCatalogProps()
    {
        //$arParams = General::getRequest();
        $arResult = cCatalog::getCategoriesProps();
        cGeneral::sendResponse($arResult);
    }

    /**
     * Метод получения списка товаров по дате обновления каталога в приложении
     * Аналогичный метод getCatalogTrades, но используется только новый метод кэширования.
     *
     * Первый запуск:
     * https://site.ru/api/v1/catalog/getCatalogTradesNew/?page=1
     *
     * Последующие запуски:
     * https://site.ru/api/v1/catalog/getCatalogTradesNew/?page=1&checkUpdateTime=1610978788
     *
     * Входные параметры:
     * ```
     * checkUpdateTime (timestamp) - время последнего обновления каталога на стороне приложения
     * page (int) - номер страницы для подгрузки списка товаров постранично в количестве 100 шт на страницу, нумерация с 1
     * ```
     *
     * Выходные данные:
     * ```
     * {
     *  add: - новые товарные позиции
     *   {
     *    "id":             код товара с торговым предложением
     *    "name":           название товара
     *    "category_id":    код категории
     *    "main_category_id": код категории 1 уровня
     *    "slides":         ['pictireUrl', 'pictireUrl'] - картинки для слайдера.
     *    "brand":          ''       - бренд
     *    "min_price":      1200   - минимальная основная цена (с учётом торговых предложений)
     *    "props":{
     *         {
     *              "id": <id свойства из списка (int)>,
     *              "value": [
     *                   "id":"<id значения из списка (int)>"
     *                ],
     *              либо
     *              "value": [
     *                   "value":"<значение (string)>"
     *                ],
     *         }
     *     },
     *     offers:[  - выгружаются только активные торговые предложения, все остальные привязанные торговые предложения необходимо убрать из каталога.
     *      {
     *        "id":             код товара
     *        "name":           название товара
     *        "picture_url": "https://site.ru/upload/iblock/71b/8e158910b53f11ea80d12cfda134a6ba_9a40809cb9eb11ea80d12cfda134a6ba.jpg",
     *        "price": 49990,
     *        "articul": "12312",
     *        "slides": [
     *            "https://site.ru/upload/iblock/d84/8e158910b53f11ea80d12cfda134a6ba_9a40809eb9eb11ea80d12cfda134a6ba.jpg",
     *            "https://site.ru/upload/iblock/caa/8e158910b53f11ea80d12cfda134a6ba_9a40809fb9eb11ea80d12cfda134a6ba.jpg",
     *            "https://site.ru/upload/iblock/e6e/8e158910b53f11ea80d12cfda134a6ba_9a4080a0b9eb11ea80d12cfda134a6ba.jpg",
     *            "https://site.ru/upload/iblock/a57/8e158910b53f11ea80d12cfda134a6ba_a0a1ab4eb9eb11ea80d12cfda134a6ba.jpg"
     *        ],
     *        "props": [
     *          {
     *          "id": 183,
     *          "values":
     *            {
     *              "id": 300
     *            }
     *          }
     *        ]
     *      }
     *    ]
     *   },
     *   {
     *    "id":             код товара без торгового предложения
     *    "name":           название товара
     *    "category_id":    код категории
     *    "slides":         ['pictireUrl','pictireUrl'] - картинки для слайдера (размер???).
     *    "brand":          ''       - бренд
     *    "min_price":      1200   - минимальная основная цена (с учётом торговых предложений)
     *    "price":          1200   - минимальная основная цена (с учётом торговых предложений)
     *    "articul":        '12312'
     *    "props":{
     *         {
     *              "id": <id свойства из списка (int)>,
     *              "value": [
     *                   "id":"<id значения из списка (int)>"
     *                ],
     *              либо
     *              "value": [
     *                   "value":"<значение (string)>"
     *                ],
     *         }
     *     },
     *
     *    ]
     *   },
     *  ],
     *  "remove": [
     *   {
     *    "id": 249788,
     *   }
     *  ], - указаны только необходимые данные для удаления
     *  "updateTime": 1610978788 (timestamp) - время последнего изменения каталога.
     *     Если указан checkUpdateTime, то после указанной даты. Дата будет выдаваться для параметра page=1 или без него.
     *  "countByPage":100 (int) - количество товаров на каждой странице,  одинаковая на всех страницах иначе будет ошибка в приложении.
     *
     * }
     * ```
     *
     * @version 0.9
     */
    /**
     * @OA\Schema(
     *     schema="TradeModel",
     *     type="object",
     *              @OA\Property( property="id", type="integer", description="id товара", example=245825),
     *              @OA\Property( property="name", type="string", description="название товара", example="Телефон BQ 1842 Tank mini"),
     *              @OA\Property( property="picture_url", type="string", description="ссылка на картинку товара", example="https://stimul.tel/upload/iblock/e80/d701nijdkf4pj8snzwqcw8jyx4ehejmq/cfaeed41afa411ea80d02cfda134a6ba_b55f1993e2e011ea80d32cfda134a6ba.jpeg"),
     *              @OA\Property( property="slides", type="array", description="картинки для слайдера",
     *                  @OA\Items(anyOf={@OA\Schema(type="string")}),
     *                  example={"https://stimul.tel/upload/iblock/ef9/cfaeed41afa411ea80d02cfda134a6ba_b55f1993e2e011ea80d32cfda134a6ba.jpeg"}
     *              ),
     *              @OA\Property( property="brand", type="string", description="имя бренда", example="BQ"),
     *              @OA\Property( property="category_id", type="integer", description="id категории", example=693),
     *              @OA\Property( property="main_category_id", type="integer", description="id категории 1 уровня", example=692),
     *              @OA\Property( property="props", type="array", description="массив свойств и значений",
     *                  @OA\Items(type="object", oneOf={
     *                      @OA\Schema(
     *                       @OA\Property( property="id", type="integer", description="id свойства из списка св-в, полученного методом getCatalogProps", example=2932),
     *                       @OA\Property( property="values",type="object", description="массив свойств и значений",
     *                              @OA\Property( property="id", type="integer", description="id значения свойства", example=246166),
     *                       )
     *                      ),
     *                      @OA\Schema(
     *                       @OA\Property( property="id", type="integer", description="id свойства из списка св-в, полученного методом getCatalogProps", example=2890),
     *                       @OA\Property( property="values",  type="object", description="массив свойств и значений",
     *                              @OA\Property(  property="value", type="string", description="значение свойства", example="Да"),
     *                       )
     *                     )
     *                   }
     *                  ),
     *              ),
     *              @OA\Property( property="offers", type="array", description="массив свойств и значений",
     *                  @OA\Items(type="object",
     *                          @OA\Property( property="id", type="integer", description="id товара", example=245825),
     *                          @OA\Property( property="name", type="string", description="название товара", example="Телефон BQ 1842 Tank mini"),
     *                          @OA\Property( property="picture_url", type="string", description="ссылка на картинку товара", example="https://stimul.tel/upload/iblock/e80/d701nijdkf4pj8snzwqcw8jyx4ehejmq/cfaeed41afa411ea80d02cfda134a6ba_b55f1993e2e011ea80d32cfda134a6ba.jpeg"),
     *                          @OA\Property( property="price", type="number", description="цена со скидкой для торгового предложения", example=1690),
     *                          @OA\Property( property="articul", type="string", description="артикул товара", example="0178704"),
     *                          @OA\Property( property="slides", type="array", description="картинки для слайдера",
     *                              @OA\Items(anyOf={@OA\Schema(type="string")}),
     *                              example={
     *                                          "https://stimul.tel/upload/iblock/b45/659d68a1afa011ea80d02cfda134a6ba_b1c3325bb1e811ea80d12cfda134a6ba.jpeg",
     *                                          "https://stimul.tel/upload/iblock/eae/659d68a1afa011ea80d02cfda134a6ba_b1c33259b1e811ea80d12cfda134a6ba.jpg"
     *                                      }
     *                          ),
     *                          @OA\Property( property="props", type="array", description="массив свойств и значений",
     *                              @OA\Items(type="object", oneOf={
     *                                  @OA\Schema(
     *                                      @OA\Property( property="id", type="integer", description="id свойства из списка св-в, полученного методом getCatalogProps", example=2932),
     *                                      @OA\Property( property="values",type="object", description="массив свойств и значений",
     *                                          @OA\Property( property="id", type="integer", description="id значения свойства", example=246166),
     *                                      )
     *                                  ),
     *                                  @OA\Schema(
     *                                      @OA\Property( property="id", type="integer", description="id свойства из списка св-в, полученного методом getCatalogProps", example=2890),
     *                                      @OA\Property( property="values",  type="object", description="массив свойств и значений",
     *                                          @OA\Property(  property="value", type="string", description="значение свойства", example="Да"),
     *                                  )
     *                                 )
     *                              }
     *                            ),
     *                         ),
     *                  ),
     *              ),
     *              @OA\Property( property="min_price", type="number", description="минимальная цена для торговых предложений", example=1690),
     * ),
     * @OA\Get(
     *     path="/api/v1/catalog/getCatalogTradesNew/",
     *     summary="Метод получения списка товаров по дате обновления каталога в приложении.",
     *     description="Метод получения списка товаров по дате обновления каталога в приложении. Первый запуск может занимать продолжительное время в зависимости от нагрузки.

    Первый запуск:
    /api/v1/catalog/getCatalogTradesNew/?page=1

    Последующие запуски:
    /api/v1/catalog/getCatalogTradesNew/?page=1&checkUpdateTime=1610978788",
     *     tags={"Каталог"},
     *     @OA\Parameter (
     *          name="page",
     *          in="query",
     *          required=false,
     *          description="номер страницы, без указания параметра будет первая страница",
     *          @OA\Schema(type="integer",default=1,minimum=1),
     *     ),
     *     @OA\Parameter (
     *          name="checkUpdateTime",
     *          in="query",
     *          required=false,
     *          example="",
     *          description="время последнего обновления каталога приложением в формате временной метки, Пример: 1610978788",
     *          @OA\Schema(ref="#/components/schemas/timestamp"),
     *     ),
     *     @OA\Response(
     *      response="200",
     *      description="Возвращает постранично список товаров каталога.",
     *      @OA\JsonContent(
     *        @OA\Property(
     *           property="add",
     *           type="array",
     *           @OA\Items(
     *              ref="#/components/schemas/TradeModel"
     *          )
     *        ),
     *        @OA\Property(
     *           property="remove",
     *           type="array",
     *           @OA\Items(
     *              type="object",
     *              @OA\Property( property="id", type="integer", description="id товара, который необходимо удалить из базы приложения", example=245709),
     *          )
     *        ),
     *        @OA\Property( property="updateTime", @OA\Schema(ref="#/components/schemas/timestamp"), description="время последнего изменения каталога. Если указан checkUpdateTime, то после указанной даты. Дата будет выдаваться для параметра page=1 или без него", example=1677545898),
     *        @OA\Property( property="countByPage", type="integer", description=" количество товаров на каждой странице,одинаковая на всех страницах, иначе будет ошибка в приложении.", example=100),
     *      )
     *    ),
     *     @OA\Response(
     *      response="400",
     *      description="Информация об ошибках, будет доступен список ошибок.

    Список кодов ошибок:
    -1 - Нетипизированная ошибка, смотрите текст сообщения
    49 - Некорректно заполнены обязательные параметры: [список полей запроса]
    ",
     *      @OA\JsonContent(ref="#/components/schemas/ErrorModel")
     *     ),
     *    @OA\Response(
     *          response="500",
     *          ref="#/components/responses/500"
     *    ),
     * )
     */
    public function getCatalogTradesNew()
    {
        $arParams = cGeneral::getRequest();

        if ($arParams !== false) {
            $page = $arParams['parameters']['page'] ?: 1;
            $checkUpdateTime = $arParams['parameters']['checkUpdateTime'] ?: 0;

            $page_count = 100;
            $arResult = cCatalog::getCatalogTradesNew($checkUpdateTime, $page, $page_count);
            cGeneral::sendResponse($arResult);
        }
    }

}