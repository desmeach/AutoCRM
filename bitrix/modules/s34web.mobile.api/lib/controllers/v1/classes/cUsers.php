<?php
namespace s34web\Mobile\Api\controllers\v1\classes;
/**
 * MainDEV: Alex Rilkov
 */

use Bitrix\Catalog\ProductTable;
use Bitrix\Highloadblock\HighloadBlockTable;
use Bitrix\Iblock\ElementTable;
use Bitrix\Iblock\Iblock;
use Bitrix\Iblock\Model\Section;
use Bitrix\Iblock\PropertyTable;
use Bitrix\Iblock\SectionPropertyTable;
use Bitrix\Iblock\SectionTable;
use Bitrix\Main\Config\Option;
use Bitrix\Main\Loader;
use Bitrix\Main\LoaderException;
use Bitrix\Main\ORM\Fields\Relations\Reference;
use Bitrix\Main\ORM\Query\Join;
use Bitrix\Main\Type\DateTime;
use CCatalogSku;
use CFile;
use CIBlockElement;
use CIBlockProperty;
use CIBlockPropertyEnum;
use CIBlockSection;
use CRSGBinds;
use CRSGGroups;
use CSite;
use Exception;
use lib\Controllers\BranchesController;
use lib\Controllers\ClientsController;
use lib\Controllers\ProductsController;

/*
Подсистема Каталог (CATALOG)
1. получение списка категорий товаров для главной getCategoriesMain()
поля:
- название
- код
- картинка

2. получение списка категорий товаров для каталога getCategories(category_id)
поля:
- название
- код
- картинка

3. получение списка товаров по категории c фильтром getTradesListByFilter(category_id, filter_params)
поля:
- код (id)
- картинки для слайдера (размер???)
- бренд (значение св-ва BREND)
- название
- основная цена (форматированная цена с валютой, цена отдельно в виде числа)
- акционная цена (форматированная цена с валютой, цена отдельно в виде числа)

4. получение информации по карточке товара getTradeDetail(id)
поля:
- слайдер
- код
- св-ва торговых предложений (название, значение)
- св-ва по группам
- основная цена (форматированная цена с валютой, цена отдельно в виде числа)
- акционная цена (форматированная цена с валютой, цена отдельно в виде числа)

5. фильтр по свойствам товаров брендам (лого?)
поля:
- название св-ва
- код св-ва
- значения св-ва (активность, текст, код значения)
*/
class cUsers
{
    public const CACHE_ID = "users";
    //const PRODUCTS_IBLOCK_ID = 1;

    public const INCLUDED_PROPERTIES = [
        'BRAND'
    ];

    public const EXCLUDED_PROPERTIES = [
        'MINIMUM_PRICE',
        'MAXIMUM_PRICE',
        'VES_G',
        'BREND',
        'TSVET',
        'HIDE_DELIVERY_LINK',
        'SHOW_ON_INDEX_PAGE',
        'CML2_LINK',
        'CML2_BAR_CODE',
        'CML2_MANUFACTURER',
        'CML2_BASE_UNIT',
        'CML2_TRAITS',
        'CML2_TAXES',
        'CML2_ATTRIBUTES',
        'CML2_ARTICLE',
        'TSVET_1',
        'OBLAST',
        'EXTENDED_REVIEWS_COUNT',
        'EXTENDED_REVIEWS_RAITING',
        'KREDIT',
        'EXTERNAL_TIMESTAMP',
        'POKAZAT_NA_GLAVNOY',
        'LINK_SALE',
        'IN_STOCK',
        'vote_count',
        'rating',
        'vote_sum',
        'SERVICES',
        'FORUM_MESSAGE_CNT',
        'ASSOCIATED',
        'ASSOCIATED_FILTER',
        'SALE_TEXT',
    ];

    private const VALID_PROP_TYPE = ["L","S","N","E"];
    private const OFFERS_PROPS = [
        'COLOR_REF',
        'OBEM_VNUTRENNEY_PAMYATI',
        'OBEM_OPERATIVNOY_PAMYATI',
        'MORE_PHOTO',
        'CML2_BAR_CODE'
    ];

  /**
     * Метод для получения списка всех активных категорий Каталога для главной страницы
     * Выгружаем активные категории по умолчанию
     * @return ResultData
     */
    public static function getCategoriesMain()
    {
        $result = new ResultData();

        $action = function () {
            return self::getItemsSections(cGeneral::getIBlockIDByLang("catalog"), 0);
        };
        $data = self::cachedataD7('products_' . getKey() , $action, 'products_list_' . getKey(), 604800, cGeneral::getIBlockIDByLang("products"));

        if (!$data['error'] && !$data['error_message']) {
            $result->setData($data['result']);
        } else {
            $result->setErrors($data['error'], $data['error_message']);
        }

        return $result;
    }

    /**
     * Метод для получения списка категорий Каталога
     * Выгружаем активные категории по умолчанию
     * @param int $category_id
     * @return ResultData
     */
    public static function getUserData($userPhone)
    {
        $result = new ResultData();

        if(strlen($userPhone)) {
            if (str_starts_with($userPhone, " "))
                $userPhone[0] = "+";
            elseif (!str_starts_with($userPhone, "+"))
                $userPhone = "+" .  trim($userPhone);
            if (!self::checkPhone($userPhone)) {
                $data['error'][] = cErrors::INCORRECT_PHONE;
            }
        }
        if ( $userData = ClientsController::getByPhone($userPhone) ) {
            $userDataJSON = [
                'uid' => $userData['ID'],
                'userFio' => $userData['NAME'],
                'email' => $userData['PROPERTY_EMAIL_VALUE']
            ];
            $data = $userDataJSON;

            if (!$data['error'] && !$data['error_message']) {
                $result->setData($data);
            } else {
                $result->setErrors($data['error'], $data['error_message']);
            }
        } else{
            $result->setStatusNotFoundError();
        }

        return $result;
    }

    /**
     * @return array|mixed|string[]
     */
    public static function setUserData($name, $phone, $email) {
        $result = new ResultData();
        $data = [];
        if (strlen($name) > 0) {
            $data["result"]["NAME"] = $name;
        } else {
            $name = "Пользователь " . $phone;
            $data["result"]["NAME"] = $name;
        }

        if (strlen($phone)) {
            if (str_starts_with($phone, " "))
                $phone[0] = "+";
            else if (!str_starts_with($phone, "+"))
                $phone="+".trim($phone);
            if(self::checkPhone($phone)) {
                $data["result"]["PHONE_NUMBER"] = $phone;
                $data["result"]["PERSONAL_PHONE"] = cGeneral::formatPhone($phone);
            }
            else
                $data['error'][] = cErrors::INCORRECT_PHONE;
        }

        if(!empty($data["result"])) {
            if(empty($data['error'])) {
                $arFields = [
                    'NAME' => $name,
                    'PHONE' => $phone,
                    'EMAIL' => $email,
                ];
                setLog($arFields);
                if (!$res = ClientsController::add($arFields)) {
                    if(!empty($res["error"])) {
                        $result->setStatusNotFoundError();
                        return $result;
                    } else {
                        $data['error'] = -1;
                        $data['error_message'] = $res["error"];
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

    private static function checkPhone($phone) {
        return strlen($phone)==12 && preg_match('#^\+[0-9]+$#', $phone);
    }


    /**
     * @return array
     */
    public static function getPropertyList($catalogIblockID=false): array
    {
        static $propertyList = [];

        try {
            if ($propertyList === []) {
                $featureList = [];

                Loader::includeModule('iblock');
                if(!$catalogIblockID)
                    $catalogIblockID = cGeneral::getIBlockIDByLang('catalog');
                $iterator = \Bitrix\Iblock\PropertyFeatureTable::getList(
                    [
                        'filter' => [
                            'PROPERTY.IBLOCK_ID' => $catalogIblockID,
                            'FEATURE_ID' => ['DETAIL_PAGE_SHOW','LIST_PAGE_SHOW'],
                            'IS_ENABLED' => 'Y',
                        ],
                        'select' => [
                            'ID',
                            //'FEATURE_ID',
                            //'IS_ENABLED',
                            'PROPERTY_DATA_CODE' => 'PROPERTY.CODE',
                            //'PROPERTY.IBLOCK_ID',
                        ],
                        'cache' => [
                            'ttl' => 3600,
                        ],
                    ]
                );
                while ($row = $iterator->fetch()) {
                    $featureList[] = $row['PROPERTY_DATA_CODE'];
                }
                if(!empty($featureList)){
                    $featureList = array_unique($featureList);
                }

                $option = Option::get('s34web.mobile.api', 'catalog_included_properties', '');
                if (empty($option)) {
                    $includedList = self::INCLUDED_PROPERTIES;
                    Option::set(
                        's34web.mobile.api',
                        'catalog_included_properties',
                        serialize(self::INCLUDED_PROPERTIES)
                    );
                }
                else {
                    $includedList = unserialize($option);
                }

                $option = Option::get('s34web.mobile.api', 'catalog_excluded_properties', '');
                if (empty($option)) {
                    $excludedList = self::EXCLUDED_PROPERTIES;
                    Option::set(
                        's34web.mobile.api',
                        'catalog_excluded_properties',
                        serialize(self::EXCLUDED_PROPERTIES)
                    );
                }
                else {
                    $excludedList = unserialize($option);
                }

                $propertyList = array_diff(array_unique(array_merge($featureList, $includedList,self::OFFERS_PROPS)), $excludedList);

                cGeneral::dump([$featureList, $includedList, $excludedList, $propertyList], 'propertyList');
            }
        } catch (\Exception $e) {
            cGeneral::dump('Ошибка при получение списка свойств каталога: '.$e->getMessage(), '_exception');
        } finally {
            return $propertyList;
        }
    }

    /**
     * Get main catalog filter
     * @param $iblockID
     * @return array
     */
    public static function getCatalogFilter($iblockID)
    {
        static $catalogFilter = [];
        if(empty($catalogFilter)){
            // исключаем разделы по свойству
            // по задаче https://b24.34web.ru/company/personal/user/100/tasks/task/view/52187/
            $sectionFilteredId = [];
            $sectionFilter = [
                'IBLOCK_ID' => $iblockID,
                'ACTIVE' => 'Y',
                'GLOBAL_ACTIVE' => 'Y',
                'UF_EXCLUDED_MOBILE' => 0,
            ];
            $sectionEntity = Section::compileEntityByIblock($iblockID);
            $iterator = $sectionEntity::getList([
                'filter' => $sectionFilter,
                'select' => [
                    'ID',
                ],
            ]);
            while($row = $iterator->fetch()){
                $sectionFilteredId[] = $row['ID'];
            }

            $catalogFilter = [
                //'ID' => 250152, // постгарантия, для теста
                'IBLOCK_ID' => $iblockID,
                '!XML_ID' => false,
                //'=ACTIVE' => 'Y',
                //'=AVAILABLE' => 'Y',
                //'!IBLOCK_SECTION_ID'=>false,
                //'>PROPERTY_MINIMUM_PRICE' => 0,
            ];
            if(!empty($sectionFilteredId)){
                $catalogFilter['SECTION_ID'] = $sectionFilteredId;
            }
        }
        return $catalogFilter;
    }

    /**
     * magic
     * @param $iblock_id
     * @return array
     */
    public static function getPropConvertXmls($iblock_id)
    {
        $prop_convert_xmls = [];
        if (Loader::includeModule('highloadblock')) {
            $propertyIterator = PropertyTable::getList(
                [
                    'filter' => [
                        'IBLOCK_ID' => self::getIblockTrades($iblock_id),
                        '=ACTIVE' => 'Y',
                        'CODE' => 'COLOR_REF',
                    ],
                    'select' => [
                        'ID',
                        'CODE',
                        'USER_TYPE',
                        'USER_TYPE_SETTINGS'
                    ],

                ]
            );
            if ($propertyRow = $propertyIterator->fetch()) {
                $userTypeSettings = unserialize($propertyRow['USER_TYPE_SETTINGS']);
                if ($propertyRow['USER_TYPE'] == 'directory') {
                    $rsData = HighloadBlockTable::getList(
                        [
                            'filter' => [
                                'TABLE_NAME' => $userTypeSettings['TABLE_NAME']
                            ]
                        ]
                    );
                    if ($hldata = $rsData->fetch()) {
                        $AsproNextColorReferenceTable = HighloadBlockTable::compileEntity($hldata)->getDataClass();
                        $colorIterator = $AsproNextColorReferenceTable::getList(
                            [
                                'filter' => [
                                    '>ID' => '1'
                                ],
                                'select' => [
                                    'ID',
                                    'UF_XML_ID'
                                ],
                            ]
                        );
                        while ($colorRow = $colorIterator->fetch()) {
                            $prop_convert_xmls[$propertyRow['CODE']][$colorRow['UF_XML_ID']] = (int) $colorRow['ID'];
                        }
                    }
                }
            }
        }
        return $prop_convert_xmls;
    }

    /**
     * get sections with their parents
     * @param $iblock_id
     */
    public static function getTradeSectionData($iblock_id)
    {
        $return = [
            'showSubsectionsSections' => [],
            'mainParents' => [],
        ];
        $entity = Section::compileEntityByIblock($iblock_id);
        $iterator = $entity::getList(
            [
                'runtime' => [
                    new Reference(
                        'PARENTS',
                        $entity,
                        [
                            'ref.IBLOCK_ID' => 'this.IBLOCK_ID',
                            '<ref.LEFT_MARGIN' => 'this.LEFT_MARGIN',
                            '>ref.RIGHT_MARGIN' => 'this.RIGHT_MARGIN'
                        ]
                    ),
                ],
                'filter' => [
                    'IBLOCK_ID' => $iblock_id,
                    'ACTIVE' => 'Y',
                    'GLOBAL_ACTIVE' => 'Y',
                ],
                'select' => [
                    'ID',
                    'IBLOCK_SECTION_ID',
                    'DEPTH_LEVEL',
                    'LEFT_MARGIN',
                    'RIGHT_MARGIN',
                    'IBLOCK_SECTION_ID',
                    'UF_SHOW_SUBSECTION_LIST',
                    'PARENTS_ID' => 'PARENTS.ID',
                    'PARENTS_NAME' => 'PARENTS.NAME',
                    'PARENTS_DEPTH_LEVEL' => 'PARENTS.DEPTH_LEVEL',
                    'PARENTS_LEFT_MARGIN' => 'PARENTS.LEFT_MARGIN',
                    'PARENTS_RIGHT_MARGIN' => 'PARENTS.RIGHT_MARGIN',
                    'PARENTS_UF_SHOW_SUBSECTION_LIST' => 'PARENTS.UF_SHOW_SUBSECTION_LIST',
                ],
                'order' => [
                    'ID' => 'ASC',
                    'PARENTS.DEPTH_LEVEL' => 'ASC',
                ]
            ]
        );
        $parents = $sections = [];
        while ($row = $iterator->fetch()) {
            $id = (int)$row['ID'];
            $parentID = (int)$row['PARENTS_ID'];

            $parents[$id][$parentID] = [
                'ID' => $parentID,
                'UF_SHOW_SUBSECTION_LIST' => $row['PARENTS_UF_SHOW_SUBSECTION_LIST'] !== '0',
            ];
            $sections[$id] = [
                'ID' => $id,
                'IBLOCK_SECTION_ID' => (int)$row['IBLOCK_SECTION_ID'],
                'DEPTH_LEVEL' => $row['DEPTH_LEVEL'],
                'UF_SHOW_SUBSECTION_LIST' => $row['UF_SHOW_SUBSECTION_LIST'] !== '0',
            ];

            $mainParentData[$row['ID']] = [
                'parent' => (int)$row['IBLOCK_SECTION_ID'],
                'depth' => $row['DEPTH_LEVEL'],
            ];
        }

        if (!empty($mainParentData)) {
            $return['mainParents'] = self::getSectionGrandParents($mainParentData);
        }

        foreach ($sections as $id => $section) {
            $show = true;
            foreach ($parents[$id] as $parentID => $parent) {
                $show = $parentID == 0
                    ? $section['UF_SHOW_SUBSECTION_LIST']
                    : $show && $parent['UF_SHOW_SUBSECTION_LIST'];
            }
            $return['showSubsectionsSections'][$id] = [
                'parent' => (int)$section['IBLOCK_SECTION_ID'],
                'depth' => $section['DEPTH_LEVEL'],
                'show' => $show,
            ];
        }

        return $return;
    }

    /**
     * @param int $limit
     * @param int $offset
     * @param array $options
     * @return array
     */
    public static function cacheBlock(int $limit = 100, int $offset = 0, $options = [])
    {

        $productCount = 0;
        $indexData = $products = $productsXmlID = $offerXmlID = [];
        $iblock_id = cGeneral::getIBlockIDByLang('catalog');
        try{
            Loader::includeModule('iblock');
            $prop_convert_xmls = self::getPropConvertXmls($iblock_id);

            $sectionData = self::getTradeSectionData($iblock_id);
            $showSubsectionsSections = $sectionData['showSubsectionsSections'];
            $mainParents = $sectionData['mainParents'];

            $select = [
                'ID',
                'IBLOCK_ID',
                'IBLOCK_SECTION_ID',
                'NAME',
                'ACTIVE',
                'XML_ID',
                'TIMESTAMP_X_UNIX',
                'DATE_CREATE',
                'PREVIEW_TEXT',
                'PREVIEW_PICTURE',
                'DETAIL_PICTURE',
                'DETAIL_TEXT',
//                    'WEIGHT',
//                    'WIDTH',
//                    'HEIGHT',
//                    'LENGTH',
                'PRICE_1',
                'PRICE_8',
                'AVAILABLE',
            ];

            $filter = self::getCatalogFilter($iblock_id);

            if(empty($options)) {
                $iNumPage = ($limit + $offset) / $limit;
                $pagination = [
                    'iNumPage' => $iNumPage,
                    'nPageSize' => $limit,
                ];
            } elseif (!empty($options['xml_id']) && !empty($options['page'])) {
                unset($filter['!XML_ID']);
                $filter['XML_ID'] = $options['xml_id'];
                $pagination = [
                    'nTopCount' => $limit,
                ];
                $iNumPage = $options['page'];
            }

            $iterator = CIBlockElement::GetList(
                ['id' => 'ASC'],
                $filter,
                false,
                $pagination,
                $select
            );

            $isAddMainPicture = cGeneral::isAddMainPictureToSlides();
            while ($ob = $iterator->GetNextElement()) {
                $arFields = $ob->GetFields();
                $arProps = $ob->GetProperties([], ['ACTIVE' => 'Y']);

                // https://b24.34web.ru/company/personal/user/100/tasks/task/view/49665/?MID=169690#com169690
                $timestampX = (int)$arFields['TIMESTAMP_X_UNIX'];
                $extTimestampX = (int)$arProps['EXTERNAL_TIMESTAMP']['VALUE'];
                $timestamp = max($timestampX, $extTimestampX);

                $picture_url = cGeneral::getFullPath(CFile::GetPath($arFields['PREVIEW_PICTURE']));

                //if($isAddMainPicture)//у простых товаров не списка картинок, поэтому нужно выгрузить детальную картинку для карточки.
                $detail_picture_url = cGeneral::getFullPath(CFile::GetPath($arFields['DETAIL_PICTURE']));
                $slides = [];
                // get more photos
                if (
                    !empty($arProps)
                    && !empty($arProps['MORE_PHOTO'])
                    && !empty($arProps['MORE_PHOTO']['VALUE'])
                    && count($arProps['MORE_PHOTO']['VALUE']) > 0
                ) {
                    foreach ($arProps['MORE_PHOTO']['VALUE'] as $photo) {
                        if ($arProps['MORE_PHOTO']['PROPERTY_TYPE'] == 'F') {
                            $slides[] = cGeneral::getFullPath(CFile::GetFileArray($photo)['SRC']);
                        } else {
                            $slides[] = $photo;
                        }
                    }
                }

                $slides= (!empty($detail_picture_url) && $isAddMainPicture) ? array_merge([$detail_picture_url],$slides) : $slides;

                unset($arProps['MORE_PHOTO']);
                $buffer = [
                    'id' => (int)$arFields['ID'],
                    'active' => $arFields['ACTIVE'],
                    'available' => $arFields['AVAILABLE'],
                    'name' => self::decodeName($arFields['NAME']),
                    'xml_id' => $arFields['XML_ID'],
                    //'timestamp' => $timestamp,
                    'picture_url' => $picture_url,
                    'slides' => $slides,
                    'brand' => $arProps['BREND']['VALUE'], // бренд
                ];

                //get a section by hierarchy whose property 'UF_SHOW_SUBSECTION_LIST' is not equal to 0
                $sectionID = (int)$arFields['IBLOCK_SECTION_ID'];

                $buffer['category_id'] = empty($showSubsectionsSections)
                    ? $sectionID
                    : self::getShowSubsectionsSectionID($showSubsectionsSections, $sectionID);

                // main_category_id - section id with depth level of 1
                if (isset($mainParents[$sectionID])) {
                    $buffer['main_category_id'] = $mainParents[$sectionID];
                }

                //выгрузка значений всех св-в
                $props = [];
                if (count($arProps) > 0) {
                    foreach ($arProps as $props_key => $props_tmp) {
                        //@cimonov catalog properties filter
                        //if (!in_array($props_key, self::getExcludedProperties())) {
                        if (in_array($props_key, self::getPropertyList())) {
                            $values = null;
                            if (is_array($props_tmp['VALUE'])) {
                                $values = [];
                                for ($i = 0; $i < count($props_tmp['VALUE']); $i++) {
                                    $values[] = [
                                        'id' => intval($props_tmp['VALUE_ENUM_ID'][$i]),
                                        'name' => $props_tmp['VALUE'][$i],
                                        //'xml_id' => $offer_props_tmp['VALUE_XML_ID']
                                    ];
                                }
                            }
                            elseif ($props_tmp['VALUE'] != '') {
                                if ($props_tmp['PROPERTY_TYPE'] == 'E') {
                                    $values =
                                        [
                                            'id' => intval($props_tmp['VALUE']),
                                            //'value' => $props_tmp['VALUE'],
                                            //'xml_id' => $offer_props_tmp['VALUE_XML_ID']
                                        ];
                                }
                                elseif ($props_tmp['PROPERTY_TYPE'] == 'L') {
                                    $values =
                                        [
                                            'id' => intval($props_tmp['VALUE_ENUM_ID']),
                                            //'_value' => $props_tmp['VALUE'],
                                            //'xml_id' => $offer_props_tmp['VALUE_XML_ID']
                                        ];
                                }
                                elseif ($props_tmp['PROPERTY_TYPE'] == 'S') {
                                    $values =
                                        [
                                            'value' => self::decodeName($props_tmp['VALUE']),
                                        ];
                                }
                            }

                            if ($values != null) {
                                $props[] = [
                                    //'_code' => $props_key,
                                    'id' => intval($props_tmp['ID']),
                                    'values' => $values
                                ];
                            }
                        }
                    }
                }

                $buffer['props'] = $props;
                $buffer['offers'] = [];

                $arSelect = [
                    'ID',
                    'XML_ID',
                    'TIMESTAMP_X_UNIX',
                    'NAME',
                    'PREVIEW_PICTURE',
                    'PRICE_1',
                    'PRICE_8',
                ];
                if($isAddMainPicture)
                {
                    $arSelect[] =  'DETAIL_PICTURE';
                }
                $res_offers = CCatalogSKU::getOffersList(
                    $arFields['ID'],
                    $iblock_id,
                    [
                        '>PRICE' => 0,
                        '@PRICE_TYPE' => [1, 8],
                        '=AVAILABLE' => 'Y',
                        '=ACTIVE' => 'Y',
                    ],
                    $arSelect,
                    ['CODE' => self::OFFERS_PROPS]
                );



                if(!empty($res_offers) && is_array($res_offers)){
                    $min_price = 0;

                    foreach ($res_offers[$arFields['ID']] as $offer_id => $offer_data) {

                        $slides = [];
                        $offerTimestamp = $offer_data['TIMESTAMP_X_UNIX'];
                        if($offerTimestamp > $timestamp){
                            $timestamp = $offerTimestamp;
                        }
                        $picture_url = cGeneral::getFullPath(
                            CFile::GetFileArray($offer_data['PREVIEW_PICTURE'])['SRC']
                        );
                        if($isAddMainPicture)
                            $detail_picture_url = cGeneral::getFullPath(
                                CFile::GetFileArray($offer_data['DETAIL_PICTURE'])['SRC']
                            );
                        // get more photos
                        if (
                            !empty($offer_data['PROPERTIES'])
                            && !empty($offer_data['PROPERTIES']['MORE_PHOTO'])
                            && !empty($offer_data['PROPERTIES']['MORE_PHOTO']['VALUE'])
                            && count($offer_data['PROPERTIES']['MORE_PHOTO']['VALUE']) > 0
                        ) {
                            foreach ($offer_data['PROPERTIES']['MORE_PHOTO']['VALUE'] as $photo) {
                                if ($offer_data['PROPERTIES']['MORE_PHOTO']['PROPERTY_TYPE'] == 'F') {
                                    $slides[] = cGeneral::getFullPath(CFile::GetFileArray($photo)['SRC']);
                                } else {
                                    $slides[] = $photo;
                                }
                            }
                            unset($offer_data['PROPERTIES']['MORE_PHOTO']);
                        }
                        //добавление картинки базовой в карточку товара
                        if(!empty($detail_picture_url) && $isAddMainPicture)
                        {
                            $slides = array_merge([$detail_picture_url],$slides);
                        }

                        //get barcode
                        $offer_data['CML2_BAR_CODE'] = $offer_data['PROPERTIES']['CML2_BAR_CODE'];
                        unset($offer_data['PROPERTIES']['CML2_BAR_CODE']);

                        $offer_props = [];
                        if (count($offer_data['PROPERTIES']) > 0) {
                            foreach ($offer_data['PROPERTIES'] as $offer_props_key => $offer_props_tmp) {
                                $values = null;
                                if (is_array($offer_props_tmp['VALUE'])) {
                                    $values = [];
                                    for ($i = 0; $i < count($offer_props_tmp['VALUE']); $i++) {
                                        $values[] = [
                                            'id' => intval($offer_props_tmp['VALUE_ENUM_ID'][$i]),
                                            'name' => $offer_props_tmp['VALUE'][$i],
                                            //'xml_id' => $offer_props_tmp['VALUE_XML_ID']
                                        ];
                                    }
                                }
                                elseif ($offer_props_tmp['VALUE'] != '') {
                                    if ($offer_props_tmp['USER_TYPE'] == 'directory') {
                                        if ($prop_convert_xmls[$offer_props_key]) {
                                            $hl_id = (int) $prop_convert_xmls[$offer_props_key][$offer_props_tmp['VALUE']];
                                            if ($hl_id > 0) {
                                                $values =
                                                    [
                                                        'id' => $hl_id,
                                                        //'code' => $offer_props_tmp['VALUE'],
                                                    ];
                                            }
                                        }
                                    }
                                    else {
                                        $values =
                                            [
                                                'id' => floatval($offer_props_tmp['VALUE_ENUM_ID']),
                                                //'value' => $offer_props_tmp['VALUE'],
                                            ];
                                    }
                                }
                                if ($values != null) {
                                    $offer_props[] = [
                                        'id' => intval($offer_props_tmp['ID']),
                                        'values' => $values
                                    ];
                                }
                            }

                        }

                        $offer = [
                            'id' => (int)$offer_data['ID'],
                            'xml_id' => $offer_data['XML_ID'],
                            'name' => self::decodeName($offer_data['NAME']),
                            //'timestamp_x' => $offerTimestamp,
                            'picture_url' => $picture_url,
                            'price' => floatval($offer_data['PRICE_1']),
                            'articul' => $offer_data['CML2_BAR_CODE']['VALUE'],
                            'slides' => $slides,
                            'props' => $offer_props
                        ];
                        if ($min_price > $offer['price'] || $min_price == 0) {
                            $min_price = $offer['price'];
                        }

                        $offerXmlID[] = $offer_data['XML_ID'];

                        $buffer['offers'][] = $offer;
                    }
                    $buffer['min_price'] = $min_price;
                }
                else {
                    $buffer['min_price'] = floatval($arFields['PRICE_1']) ?: 0;
                    $buffer['price'] = $buffer['min_price'];
                    $buffer['articul'] = $arProps['CML2_BAR_CODE']['VALUE'];
                }

                $buffer['timestamp_x'] = $timestamp;

                $products[] = $buffer;
                $productsXmlID[] = $arFields['XML_ID'];
            }

            $productCount = count($products);

            if(!empty($products)){
                $indexData = [
                    'page' => $iNumPage,
                    'count' => $productCount,
                    'products' => $productsXmlID,
                    'offers' => $offerXmlID
                ];

                cGeneral::setJson($products, cGeneral::getCacheDirectoryPath(), 'block' . $iNumPage . '.json');
                //cGeneral::setIndexJson($indexData, $directoryFull, 'index.json');
            }

        } catch (Exception $e) {
            $error_message = $e->getMessage();
            file_put_contents($_SERVER["DOCUMENT_ROOT"]."/logs/last_errors.log", $error_message);
        } finally {
            //return $productCount;
            return $indexData;
        }
    }

    public static function getLastModifyTime($filter)
    {
        $timestamp = 0;
        $iterator = CIBlockElement::GetList(
            ['TIMESTAMP_X' => 'DESC'],
            $filter,
            false,
            ['nTopCount' => 1],
            ['TIMESTAMP_X_UNIX']
        );
        if ($row = $iterator->Fetch()) {
            $timestamp = $row['TIMESTAMP_X_UNIX'];
        }
        return $timestamp;
    }

    /**
     * @return int
     */
    public static function getCatalogCount(): int
    {
        $iblock_id = cGeneral::getIBlockIDByLang('catalog');
        $filter = self::getCatalogFilter($iblock_id);

        // todo сравнить с получением кол-ва из d7
        return (int) CIBlockElement::GetList([], $filter, []);
        //print_r($count . PHP_EOL);
        //return intdiv($count, $limit);
    }

    public static function getCatalogData()
    {
        $data = [];
        $iblock_id = cGeneral::getIBlockIDByLang('catalog');
        $filter = self::getCatalogFilter($iblock_id);
        $select = [
            'ID',
            'IBLOCK_ID',
            'XML_ID',
        ];
        $iterator = CIBlockElement::GetList([], $filter, false, false, $select);
        while($row = $iterator->Fetch()){
            $products[$row['XML_ID']] = $row['ID'];
        }

        if(!empty($products)) {
            $offers = CCatalogSKU::getOffersList(
                $products,
                $iblock_id,
                [
                    '>PRICE' => 0,
                    '@PRICE_TYPE' => [1, 8],
                    '=AVAILABLE' => 'Y',
                    '=ACTIVE' => 'Y',
                ],
                [
                    'ID',
                    'IBLOCK_ID ',
                    'XML_ID',
                ]
            );

            foreach ($products as $xmlID => $productID) {
                $data['products'][] = $xmlID;
                if (isset($offers[$productID])) {
                    foreach ($offers[$productID] as $offer) {
                        $data['offers'][] = $offer['XML_ID'];
                        $data['links'][$offer['XML_ID']] = $xmlID;
                    }
                }
            }
        }

        return $data;
    }

    public static function checkLastUpdate($timestamp)
    {
        $catalogIblockID = cGeneral::getIBlockIDByLang('catalog');
        $offerIblockID = cGeneral::getIBlockIDByLang('offers');
        $lastUpdateDateTime = DateTime::createFromTimestamp($timestamp);

        $needUpdated = [];

        $iblockEntity = Iblock::wakeUp($catalogIblockID);
        $catalogEntityClass = $iblockEntity->getEntityDataClass();
        $iterator = $catalogEntityClass::getList(
            [
                'filter' => [
                    '!XML_ID' => false,
                    //'=ACTIVE' => 'Y',
                    '!IBLOCK_SECTION_ID' => false,
                    //'=CATALOG_DATA.AVAILABLE' => 'Y',
                    'CATALOG_DATA.TYPE' => ProductTable::TYPE_PRODUCT,
                    [
                        'LOGIC' => 'OR',
                        [
                            '>TIMESTAMP_X' => $lastUpdateDateTime,
                        ],
                        [
                            '>EXTERNAL_TIMESTAMP.VALUE' => $lastUpdateDateTime->format('Y-m-d H:i:s'),
                        ],
                    ],
                ],
                'select' => [
                    //'NAME',
                    'XML_ID',
                    //'TIMESTAMP_X',
                    //'EXTERNAL_TIMESTAMP_' => 'EXTERNAL_TIMESTAMP',
                    //'CATALOG_DATA'
                ],
                'runtime' => [
                    new Reference(
                        'CATALOG_DATA',
                        ProductTable::class,
                        Join::on('this.ID', 'ref.ID')
                    ),
                ],
            ]
        );

        while($row = $iterator->fetch()){
            $needUpdated[] = $row['XML_ID'];
            //$needUpdated[] = $row;
        }


        $iblockEntity = Iblock::wakeUp($offerIblockID);
        $offerEntityClass = $iblockEntity->getEntityDataClass();
        $iterator = $offerEntityClass::getList(
            [
                'filter' => [
                    '!XML_ID' => false,
                    '=ACTIVE' => 'Y',
                    '!MAIN_PRODUCT.XML_ID' => false,
                    //'=MAIN_PRODUCT.ACTIVE' => 'Y',
                    '!MAIN_PRODUCT.IBLOCK_SECTION_ID' => false,
                    //'=MAIN_PRODUCT_CATALOG_DATA.AVAILABLE' => 'Y',
                    'MAIN_PRODUCT_CATALOG_DATA.TYPE' => ProductTable::TYPE_SKU,
                    [
                        'LOGIC' => 'OR',
                        [
                            '>TIMESTAMP_X' => $lastUpdateDateTime,
                        ],
                        [
                            '>MAIN_PRODUCT.TIMESTAMP_X' => $lastUpdateDateTime,
                        ],
                        [
                            '>MAIN_PRODUCT.EXTERNAL_TIMESTAMP.VALUE' => $lastUpdateDateTime->format('Y-m-d H:i:s'),
                        ],
                    ]
                ],
                'select' => [
                    'XML_ID',
                    //'TIMESTAMP_X',
                    'CML2_LINK_IBLOCK_GENERIC_VALUE' => 'CML2_LINK.IBLOCK_GENERIC_VALUE',
                    'MAIN_PRODUCT_NAME' => 'MAIN_PRODUCT.NAME',
                    'MAIN_PRODUCT_XML_ID' => 'MAIN_PRODUCT.XML_ID',
                ],
                'runtime' => [
                    new Reference(
                        'MAIN_PRODUCT',
                        $catalogEntityClass,
                        Join::on('this.CML2_LINK_IBLOCK_GENERIC_VALUE', 'ref.ID')
                    ),
                    new Reference(
                        'MAIN_PRODUCT_CATALOG_DATA',
                        ProductTable::class,
                        Join::on('this.CML2_LINK_IBLOCK_GENERIC_VALUE', 'ref.ID')
                    ),
                ],
                //'limit' => 20,
            ]
        );

        while($row = $iterator->fetch()){
            $needUpdated[] = $row['MAIN_PRODUCT_XML_ID'];
            //$test[] = $row;
        }

        //return $test;
        return $needUpdated;
    }

    /**
     * @param int $checkUpdateTime
     * @param int $page
     * @param int $count_by_page
     * @return ResultData
     */
    public static function getCatalogTradesNew(int $checkUpdateTime = 0, int $page = 1, int $count_by_page = 100): ResultData
    {
        $result = new ResultData();
        /*$action = function () use ($checkUpdateTime, $page, $count_by_page) {
            return self::getTradesListFullNew(
                cGeneral::getIBlockIDByLang('catalog'),
                $checkUpdateTime,
                $page,
                $count_by_page
            );
        };
        $data = self::cachedataD7(
            'Catalog',
            $action,
            'getCatalogTradesNew' . implode('_', [$page, $count_by_page]),
            604800,
            cGeneral::getIBlockIDByLang('catalog')
        );*/

        $data = self::getTradesListFullNew(
            cGeneral::getIBlockIDByLang('catalog'),
            $checkUpdateTime,
            $page,
            $count_by_page
        );

        if (!$data['error'] && !$data['error_message']) {
            $result->setData($data['result']);
        } else {
            $result->setErrors($data['error'], $data['error_message']);
        }
        return $result;
    }


    /*
    * Выгрузка списка св-в для фильтра и секций
    */
    public static function getCategoriesProps()
    {
        $result = new ResultData();

        $action = function () {
            return self::getSectionProps(cGeneral::getIBlockIDByLang("catalog"));
        };
        $data = self::cachedataD7('SectionProps', $action, 'getSectionProps', 604800, cGeneral::getIBlockIDByLang("catalog"));


        if (!$data['error'] && !$data['error_message']) {
            $result->setData($data['result']);
        } else {
            $result->setErrors($data['error'], $data['error_message']);
        }

        return $result;
    }

    private static function getPropsGrouping(&$props)
    {
        if(is_array($props) && count($props)>0) {
            //Получить список названий групп для группировки
            $action = function () {
                if(Loader::includeModule("redsign.grupper")) {
                    //кэширование данных
                    $arGroups = array();
                    $groups = new CRSGGroups();
                    $rsGroups = $groups->GetList(array("SORT" => "ASC", "ID" => "ASC"), array());
                    while ($arGroup = $rsGroups->Fetch()) {
                        $arGroups[$arGroup["ID"]] =
                            [
                                "name" => $arGroup["NAME"],
                                "sort" => $arGroup["SORT"]
                            ];
                    }
                    $binds = new CRSGBinds();
                    $rsBinds = $binds->GetList(array("ID" => "ASC"), array("GROUP_ID" => $arGroup["ID"]));
                    $arGroupedProps = [];
                    while ($arBind = $rsBinds->Fetch()) {
                        $arGroupedProps[$arBind["IBLOCK_PROPERTY_ID"]] = $arGroups[$arBind["GROUP_ID"]];
                    }
                    return $arGroupedProps;
                }
                return false;
            };
            $arGroupedProps = self::cachedataD7('Catalog', $action, 'getPropsGrouping', 604800, cGeneral::getIBlockIDByLang("catalog"));

            if(is_array($arGroupedProps)) {

                foreach ($props as $prop_id=>&$prop)
                {

                    if (array_key_exists($prop_id, $arGroupedProps)) {
                        $prop["group_name"] = $arGroupedProps[$prop_id]["name"];
                        $prop["group_sort"] = intval($arGroupedProps[$prop_id]["sort"]);
                    }
                }
            }

        }

    }

    private static function getSectionProps($catalog_iblock)
    {
      //Получить блок торговых предложений по id catalog
      $catalog_offers_iblock = self::getIblockTrades($catalog_iblock);
      $iblocks = [$catalog_iblock, $catalog_offers_iblock];
      $props = [];

      foreach ($iblocks as $iblock) {
          $rsProperties = CIBlockProperty::GetList(
              ["sort" => "asc", "name" => "asc"],
              ["ACTIVE" => "Y", "IBLOCK_ID" => $iblock]
          );
          $props_data = [];
          while ($arPropsFields = $rsProperties->GetNext()) {
              if (
                  //@cimonov catalog properties filter
                  //!in_array($arPropsFields["CODE"], self::$exclude_props)
                  in_array($arPropsFields['CODE'], self::getPropertyList($iblock))
                  && in_array($arPropsFields["PROPERTY_TYPE"], self::VALID_PROP_TYPE)
              ) {

                  $props[intval($arPropsFields["ID"])] = [
                      "id" => intval($arPropsFields["ID"]),
                      "code" => $arPropsFields["CODE"],
                      "name" => self::clearPropName($arPropsFields["NAME"]),
                      "type" => $arPropsFields["PROPERTY_TYPE"],
                      "filter_show" => false,
                  ];

                  if($arPropsFields["LINK_IBLOCK_ID"]>0 && $arPropsFields["PROPERTY_TYPE"]=="E")
                  {
                      $props_data[intval($arPropsFields["ID"])] = [
                          "link_iblock" => $arPropsFields["LINK_IBLOCK_ID"],
                      ];
                  }
                  //$props[intval($arPropsFields["ID"])]["_full"] = $arPropsFields;
                  if($arPropsFields["USER_TYPE"] == "directory")
                  {
                      $props[intval($arPropsFields["ID"])]["type"] = "LE";
                      $props_data[intval($arPropsFields["ID"])] = [
                          "table" => $arPropsFields["USER_TYPE_SETTINGS"]["TABLE_NAME"],
                      ];
                  }
              }
          }

          if (is_array($props)) {
              self::getPropsGrouping($props);

              $list = SectionPropertyTable::getList(
                  [
                      'select' => [
                          //'IBLOCK.CODE',
                          //'SECTION.*',
                          'SECTION.ID',
                          //'SECTION.NAME',
                          'PROPERTY.ID',
                          'PROPERTY.NAME',
                          //'IBLOCK_ID',
                          'SECTION_ID',  //!
                          'PROPERTY_ID', //!
                          //'SMART_FILTER',
                          //'DISPLAY_TYPE',
                          //'DISPLAY_EXPANDED',
                          //'FILTER_HINT',
                      ],
                      'filter' => [
                          'IBLOCK_ID' => $iblock,
                          //'SECTION_ID'=>0,
                          'SMART_FILTER' => "Y"
                      ],
                      'order' => [
                          'SECTION_ID' => 'ASC'
                      ],
                      //'limit' => 10
                  ]
              );
              while ($row = $list->fetch()) {
                  if (is_array($props[$row['PROPERTY_ID']])) {
                      $p = &$props[$row['PROPERTY_ID']];
                      //$p['filter_view_type'] = $row['DISPLAY_TYPE'];
                      $p['filter_show'] = true;
                      $p['filter_section_id'] = intval($row['SECTION_ID']);
                  }
              }
              foreach ($props as $prop_id=>&$prop)
              {
                  if (in_array($prop["type"], ["L"]) && !empty($prop["code"]))
                  {
                      $rsValues = CIBlockPropertyEnum::GetList(
                          ["sort" => "asc", "value" => "asc"],
                          [
                              //"IBLOCK_ID" => $iblock,
                              "CODE" => $prop["code"]
                          ]
                      );
                      $prop['values'] = [];
                      while ($arValues = $rsValues->Fetch()) {
                          $prop['values'][] = [
                              "id" => intval($arValues["ID"]),
                              "value" => trim($arValues["VALUE"])
                          ];
                      }
                      unset($arValues);
                  }elseif (in_array($prop["type"], ["LE"]) && !empty($prop["code"]))
                  {
                      $prop["type"] = "LP";
                      if (Loader::includeModule('highloadblock')) {

                          $rsData = HighloadBlockTable::getList(array('filter' => array('TABLE_NAME' => $props_data[$prop_id]["table"])));
                          if ($hldata = $rsData->fetch()) {
                              $entity = HighloadBlockTable::compileEntity($hldata);
                              $entity_data_class = $entity->getDataClass();
                              $res = $entity_data_class::getList(['select' => array("*"),"filter"=>[">ID"=>"1"]]);
                              $prop['values'] = [];
                              while ($row = $res->fetch())
                              {
                                  $color_file = CFile::GetFileArray($row["UF_FILE"]);
                                  $prop['values'][] = [
                                      "id"=>intval($row["ID"]),
                                      "value" => trim($row["UF_NAME"]),
                                      "picture_url" => is_array($color_file) && !empty($color_file["SRC"])?cGeneral::getFullPath($color_file["SRC"]):null
                                  ];
                              }
                          }
                      }
                  }elseif (in_array($prop["type"], ["E"]) && !empty($prop["code"]))
                  {
                      $prop["type"] = "LP";
                      $brand_iblock = intval($props_data[$prop_id]['link_iblock']);
                      if ($brand_iblock>0) {
                          $dbItems = ElementTable::getList(array(
                                                                             'select' => array('ID', 'NAME','PREVIEW_PICTURE'),
                                                                             'filter' => array('IBLOCK_ID' => $brand_iblock,"ACTIVE"=>"Y"),
                                                                             //'limit' => 100,
                                                                             'order' => array('SORT' => 'ASC')
                                                                         ));
                          $prop['values'] = [];
                          while ($arItem = $dbItems->fetch()) {
                              $picture_file = CFile::GetFileArray($arItem["PREVIEW_PICTURE"]);
                              $prop['values'][] = [
                                  "id"=>intval($arItem["ID"]),
                                  "value" => trim($arItem["NAME"]),
                                  "picture_url" => is_array($picture_file) && !empty($picture_file["SRC"])?cGeneral::getFullPath($picture_file["SRC"]):null,

                              ];
                          }
                      }
                  }
              }
          }
      }
      return ['result' => array_values($props), 'error' => 0, 'error_message' => ""];
    }

    private static function getSectionGrandParents($parents){
        $parentsSorted = [];
        foreach ($parents as $id => $parent) {
            if ($parent['depth'] == 1) {
                $parentsSorted[$id] = $parent['parent'];
            } elseif ($parents[$parent['parent']]['depth'] == 1) {
                $parentsSorted[$id] = $parent['parent'];
            } else {
                $parentID = self::getSectionGrandParent($parents, $id);
                $parentsSorted[$id] = $parentID;
            }
        }
        return $parentsSorted;
    }

    private static function getSectionGrandParent($parents, $id){
        if($parents[$id]['depth'] == 1){
            $parentID = $id;
        } else {
            $parentID = self::getSectionGrandParent($parents, $parents[$id]['parent']);
        }
        return $parentID;
    }

    private static function getSectionTree($iblock_id)
    {
        $error = false;
        $error_message = false;
        $result = [];
        try {
            if (Loader::includeModule('iblock')) {
                $arFilter = [
                    'IBLOCK_ID' => $iblock_id,
                    'ACTIVE' => 'Y',
                    'GLOBAL_ACTIVE' => 'Y',
                    'UF_EXCLUDED_MOBILE' => 0,
                ];
                $arSelect = [
                    'ID',
                    'NAME',
                    'DEPTH_LEVEL',
                    'PICTURE',
                    'IBLOCK_SECTION_ID',
                    'UF_SHOW_SUBSECTION_LIST',
                    'UF_EXCLUDED_MOBILE',
                ];
                $rsSection = CIBlockSection::GetTreeList($arFilter, $arSelect);
                $section_link = [];
                while ($row = $rsSection->Fetch()) {
                    $parents[$row['ID']] = [
                        'parent' => (int) $row['IBLOCK_SECTION_ID'],
                        'depth' => $row['DEPTH_LEVEL'],
                    ];
                    $sections[$row['ID']] = $row;
                }

                if(!empty($parents)) {
                    $parentsSorted = self::getSectionGrandParents($parents);
                }

                if(!empty($sections)) {
                    $sectionsFiltered = [];
                    foreach ($sections as $section) {
                        if(!isset($sections[$section['IBLOCK_SECTION_ID']]) || $sections[$section['IBLOCK_SECTION_ID']]['UF_SHOW_SUBSECTION_LIST'] !== '0'){
                            $sectionsFiltered[] = $section;
                        }
                    }
                }

                if(!empty($sectionsFiltered)) {
                    foreach ($sectionsFiltered as $arSection) {
                        $id = (int)$arSection['ID'];
                        $section = [
                            'id' => $id,
                            'name' => trim($arSection['NAME']),
                        ];
                        if (
                            $arSection['DEPTH_LEVEL'] > 1
                            && isset($parentsSorted[$id])
                        ) {
                            $section['main_category_id'] = $parentsSorted[$id];
                        }

                        if ($arSection['PICTURE'] > 0) {
                            $section['icon'] = cGeneral::getFullPath(CFile::getPath($arSection['PICTURE']));
                        }
                        $section['child'] = [];
                        $section_link[$section['id']] = &$section['child'];

                        //Добавить выгрузку параметров

                        if ($arSection['DEPTH_LEVEL'] > 1) {
                            $section_link[$arSection['IBLOCK_SECTION_ID']][] = $section;
                        } else {
                            $result[] = $section;
                        }
                    }
                }
                unset($section_link);
            } else {
                $error = cErrors::MODULE_IBLOCK_NOT_LOADED;
            }
        } catch (Exception $e) {
            $error_message = $e->getMessage();
        }
        return ['result' => $result, 'error' => $error, 'error_message' => $error_message];
    }

    private static function getShowSubsectionsSectionID($showSubsectionsSections, $id){
        if($showSubsectionsSections[$id]['depth'] <= 1){
            $showSubsectionsSectionID = $id;
        } elseif($showSubsectionsSections[$id]['show']) {
            $showSubsectionsSectionID = $id;
        } else {
            $showSubsectionsSectionID = self::getShowSubsectionsSectionID($showSubsectionsSections, $showSubsectionsSections[$id]['parent']);
        }
        return $showSubsectionsSectionID;
    }

    /**
     * @param int $iblock_id
     * @param int $checkUpdateTime
     * @param int $page
     * @param int $count_by_page
     * @return array
     */
    private static function getTradesListFullNew(int $iblock_id, int $checkUpdateTime = 0, int $page = 1, int $count_by_page = 100): array
    {
        $error = false;
        $error_message = false;
        $result = [
            'add' => [],
            'remove' => [],
        ];

        $files = cGeneral::getFiles(['.', '..', 'index.json']);

        if(empty($files)){
            $count = Agent::$limit;
            $offset = 0;
            while($count == Agent::$limit){
                $indexData = self::cacheBlock(Agent::$limit, $offset);
                $count = $indexData['count'];
                cGeneral::setIndexJson($indexData, cGeneral::getCacheDirectoryPath(), 'index.json');
                $offset += $count;
                //cGeneral::dump([$count, $offset], '_cache_catalog_1');
            }

            $files = cGeneral::getFiles(['.', '..', 'index.json']);
        }

        $getByUpdateTime = $checkUpdateTime > 0;
        $getByPage = $page > 1;

        $cachedData = cGeneral::getCachedData();

        //cGeneral::dump([$getByUpdateTime, $getByPage, $cachedData], '_blocks');

        if(!empty($cachedData) && is_array($cachedData)){
            $buffer = [];

            if($getByUpdateTime && $getByPage) {
                $count = $page * $count_by_page;

                foreach ($files as $fileName) {
                    $block = cGeneral::getJson(cGeneral::getCacheDirectoryPath() . 'cCatalog.php/' . $fileName);
                    if(!empty($block) && is_array($block)) {
                        foreach ($block as $item) {
                            if ($item['timestamp_x'] > $checkUpdateTime) {
                                $buffer[] = $item;
                            }
                            if (count($buffer) == $count) {
                                break 2;
                            }
                        }
                    }
                }

                $data = array_slice($buffer, ($page - 1) * $count_by_page, $count_by_page);
                foreach ($data as $datum) {
                    if($datum['active'] == 'N' || $datum['available'] == 'N'){
                        $result['remove'][] = ['id' => $datum['id']];
                    } else {
                        $result['add'][] = cGeneral::clearDatum($datum);
                    }
                }
            }
            elseif($getByUpdateTime){
                $count = $count_by_page;
                foreach ($files as $fileName) {
                    $block = cGeneral::getJson(cGeneral::getCacheDirectoryPath() . 'cCatalog.php/' . $fileName);
                    if(!empty($block) && is_array($block)) {
                        foreach ($block as $item) {
                            if($item['timestamp_x'] > $checkUpdateTime){
                                $buffer[] = $item;
                            }
                            if(count($buffer) == $count){
                                break 2;
                            }
                        }
                    }
                }
                //$data = $buffer;
                foreach ($buffer as $datum) {
                    if($datum['active'] == 'N' || $datum['available'] == 'N'){
                        $result['remove'][] = ['id' => $datum['id']];
                    } else {
                        $result['add'][] = cGeneral::clearDatum($datum);
                    }
                }
                $result['updateTime'] = filemtime(cGeneral::getCacheDirectoryPath(). '/index.json');
            }
            elseif($getByPage) {
                $count = $page * $count_by_page;
                foreach ($files as $fileName) {
                    $block = cGeneral::getJson(cGeneral::getCacheDirectoryPath() . 'cCatalog.php/' . $fileName);
                    if(!empty($block) && is_array($block)) {
                        foreach ($block as $item) {
                            if ($item['active'] == 'Y' && $item['available'] == 'Y') {
                                $buffer[] = $item;
                            }
                            if (count($buffer) == $count) {
                                break 2;
                            }
                        }
                    }
                }

                $data = array_slice($buffer, ($page - 1) * $count_by_page, $count_by_page);
                //$result['add'] = $data;
                foreach ($data as $datum) {
                    $result['add'][] = cGeneral::clearDatum($datum);
                }
            }
            else {
                $count = $count_by_page;
                foreach ($files as $fileName) {
                    $block = cGeneral::getJson(cGeneral::getCacheDirectoryPath() . 'cCatalog.php/' . $fileName);
                    if(!empty($block) && is_array($block)) {
                        foreach ($block as $item) {
                            if ($item['active'] == 'Y' && $item['available'] == 'Y') {
                                $buffer[] = $item;
                            }
                            if (count($buffer) == $count) {
                                break 2;
                            }
                        }
                    }
                }
                //$data = $buffer;
                foreach ($buffer as $datum) {
                    $result['add'][] = cGeneral::clearDatum($datum);
                }
                $result['updateTime'] = filemtime(cGeneral::getCacheDirectoryPath(). '/index.json');


            }
            //if(!$getByPage){ //должно быть на всех страницах одинаковое число
              //Количество элементов на странице
              $result['countByPage'] = $count_by_page;
            //}
        }

        return ['result' => $result, 'error' => $error, 'error_message' => $error_message];
    }

    private static  function clearPropName(string $name)
    {
        $name = trim($name);
        $div = strrpos($name,"_");
        if($div!==false && $div!=strlen($name))
        {
            $val = trim(substr($name, $div+1));
            if(ctype_digit($val) || strlen($val)==0)
            {
                $name = substr($name,0,$div);
            }
        }
        return $name;
    }

    private static function getIblockTrades($iblock_id)
    {
        $iblock_trade_id = 0;
        if($iblock_id) {
            $mxResult = CCatalogSKU::GetInfoByProductIBlock(
                $iblock_id
            );

            if (is_array($mxResult)) {
                $iblock_trade_id = $mxResult['IBLOCK_ID'];
            }
        }
        return $iblock_trade_id;
    }

    private static function getResizeImage($idResPicture, $needResize=true)
    {
        //$idResPicture = $arFields['PREVIEW_PICTURE'];
        $preview_picture = CFile::GetFileArray($idResPicture);

        if(!empty($preview_picture["SRC"]) && file_exists($_SERVER["DOCUMENT_ROOT"].$preview_picture["SRC"]))
        {
            if (($preview_picture["HEIGHT"] > 300 || $preview_picture["WIDTH"] > 300)  && $needResize)
            {
                $preview_picture = CFile::ResizeImageGet($idResPicture, array('width' => 300, 'height' => 300), BX_RESIZE_IMAGE_PROPORTIONAL, false);
                $preview_picture = ["SRC" => $preview_picture["src"]];
            }
            $buffer['preview_image'] = cGeneral::getFullPath($preview_picture["SRC"]);
            return $buffer['preview_image'];
        }/*else{
            $buffer['preview_image'] = General::getFullPathStandart();
        }*/
        return false;
    }

    /**
     * @param $name
     * @param $function
     * @param $cache_id
     * @param $cacheLifetime
     * @param int $iblock_id
     * @return string|array
     */
    private static function cachedataD7($name, $function, $cache_id, $cacheLifetime, $iblock_id = 0)
    {
        $cache_id .= "|lang=".cGeneral::getLang();
        return cGeneral::cacheDataIBlock(self::CACHE_ID,$name, $function, $cache_id, $cacheLifetime, $iblock_id);
        /*if (is_callable($function)) {
            if (self::IS_CACHE_ACTIVE) {
                $obCache = \Bitrix\Main\Data\Cache::createInstance();
                $cachePath = '/apicache/content/get' . $name;
                if ($obCache->initCache($cacheLifetime, $cache_id, $cachePath)) {
                    $result = $obCache->GetVars();
                } elseif ($obCache->startDataCache()) {

                    $result = $function();
                    if ($iblock_id !== 0) {
                        $GLOBALS['CACHE_MANAGER']->StartTagCache($cachePath);
                        $GLOBALS['CACHE_MANAGER']->RegisterTag("iblock_id_" . $iblock_id);
                        $GLOBALS['CACHE_MANAGER']->EndTagCache();
                    }
                    if (!empty($result)) {
                        $obCache->endDataCache($result);
                    } else {
                        $obCache->abortDataCache();
                    }
                }

            } else {
                $result = $function();
            }
            return $result;
        } else {
            return Errors::getText(Errors::NO_FUNCTION);
        }*/
    }


    /*
     * Выгрузка списка св-в для фильтра и секций
     */
    public static function getCategoriesTreeWithProps()
    {

        $result = new ResultData();
        /*if (empty($category_id)) {
            $data = ['result' => [], 'error' => Errors::NO_ID];
        } else {*/
        $action = function () {
            return self::getSectionTreeWithProps(cGeneral::getIBlockIDByLang("catalog"));
        };
        $data = self::cachedataD7('getSectionTreeWithProps', $action, 'getSectionTreeWithProps', 604800, cGeneral::getIBlockIDByLang("catalog"));
        /*}*/

        if (!$data['error'] && !$data['error_message']) {
            $result->setData($data['result']);
        } else {
            $result->setErrors($data['error'], $data['error_message']);
        }

        return $result;
    }


    static public $emptySectionXmlID = 'empty-section-xml-id';


    private static function getAllPropsBySection()
    {
        /*$properties = CIBlockProperty::GetList(Array("sort"=>"asc", "name"=>"asc"), Array("ACTIVE"=>"Y", "IBLOCK_ID"=>General::getIBlockIDByLang("catalog")));
        while ($prop_fields = $properties->GetNext())
        {
            //echo $prop_fields["ID"]." - ".$prop_fields["NAME"]."<br>";
        }*/
        $list = SectionPropertyTable::getList(
            [
                'select' => [
                    //'IBLOCK.CODE',
                    //'SECTION.*',
                    'SECTION.ID',
                    //'SECTION.NAME',
                    'PROPERTY.ID',
                    'PROPERTY.NAME',
                    //'IBLOCK_ID',
                    'SECTION_ID',
                    //'PROPERTY_ID',
                    'SMART_FILTER',
                    'DISPLAY_TYPE',
                    //'DISPLAY_EXPANDED',
                    //'FILTER_HINT',
                ],
                'filter' => [
                    'SECTION_ID'=>0,
                    'SMART_FILTER'=>"Y"
                ],
                'order' => [
                    'SECTION_ID' =>'ASC'
                ]//,
                //'limit' => 1000
            ]);
        $items = [];
        while($row = $list->fetch())
        {
            //$items[$row['SECTION_ID']][] = $row;
            $element = [
                //'IBLOCK_SECTION_PROPERTY_IBLOCK_CODE' => $row['IBLOCK_SECTION_PROPERTY_IBLOCK_CODE'],
                //'I_PROP_SECTION_XML_ID' => $row['IBLOCK_SECTION_PROPERTY_SECTION_XML_ID'],
                //'I_PROP_SECTION_ID'     => $row['IBLOCK_SECTION_PROPERTY_SECTION_ID'],
                //'I_PROP_SECTION_NAME'   => $row['IBLOCK_SECTION_PROPERTY_SECTION_NAME'],
                'ID'            => intval($row['IBLOCK_SECTION_PROPERTY_PROPERTY_ID']),
                //'SECTION_ID'  => $row['SECTION_ID'],
                'NAME'          => trim($row['IBLOCK_SECTION_PROPERTY_PROPERTY_NAME']),
                'VIEW_TYPE'     => $row['DISPLAY_TYPE'],
                'SHOW_FILTER'   => true,
            ];
            if(in_array($element,["G"]))
                $element['VALUES'] = [];
            $items[$row['SECTION_ID']][] = $element;
        }
        return $items;
    }

    private static function getSectionTreeWithProps($iblock_id)
    {
        $error = false;
        $error_message = false;
        $result = [];
        try {
            if (Loader::includeModule("iblock"))
            {
                $arFilter = array('IBLOCK_ID' => $iblock_id, 'ACTIVE' => 'Y', 'GLOBAL_ACTIVE' => 'Y');
                $arSelect = array('ID', 'NAME', 'DEPTH_LEVEL', 'PICTURE','IBLOCK_SECTION_ID');
                $rsSection = CIBlockSection::GetTreeList($arFilter, $arSelect);
                $props = self::getAllPropsBySection();
                $result["props"] = $props[0];

                while($arSection = $rsSection->Fetch())
                {
                    $section = [
                        "id"   => intval($arSection["ID"]),
                        "name" => trim($arSection["NAME"])
                    ];

                    //Получить список св-в доступных для раздела
                    //Список св-в доступных для отображения.
                    $section["props"] = $props[$section['id']];

                    if($arSection["PICTURE"] > 0)
                        $section["icon"] = cGeneral::getFullPath(CFile::getPath($arSection["PICTURE"]));
                    $section['child'] = [];
                    $section_link[$section['id']] = &$section['child'];

                    //Добавить выгрузку параметров
                    if($arSection["DEPTH_LEVEL"] > 1)
                        $section_link[$arSection["IBLOCK_SECTION_ID"]][] = $section;
                    else {
                        $items[] = $section;
                    }
                }
                $result["items"] = $items;
                unset($section_link);
            } else {
                $error = cErrors::MODULE_IBLOCK_NOT_LOADED;
            }
        } catch (Exception $e) {
            $error_message = $e->getMessage();
        }
        return ['result' => $result, 'error' => $error, 'error_message' => $error_message];
    }

    private static function getBranchesList()
    {
        $error = false;
        $error_message = false;
        $result = [];
        try {
            if (Loader::includeModule("iblock"))
            {
//                $arFilter = array(
//                    'IBLOCK_ID' => $iblock_id,
//                    //'DEPTH_LEVEL' => $depth_level,
//                    'GLOBAL_ACTIVE' => 'Y',
//                    //'INCLUDE_SUBSECTION'=>'N',
//                      'ACTIVE'=>"Y"
//                    //'CNT_ACTIVE' => 'Y',
//                    //'ELEMENT_SUBSECTIONS' => 'Y'
//                );
//                if($parent_section_id>0)
//                    $arFilter['SECTION_ID']=$parent_section_id;
//                if($depth_level>0)
//                    $arFilter['DEPTH_LEVEL']=$depth_level;
//
//                $arSelect = array("ID","IBLOCK_ID", "NAME", "IBLOCK_SECTION_ID", "SORT", "DEPTH_LEVEL", "PICTURE");
//                $rsSect = CIBlockSection::GetList(array('SORT' => 'asc','NAME'=>"asc"), $arFilter, false, $arSelect);
                $branches = BranchesController::getList();
                foreach ($branches as $branch) {
                    $branch = [
                        'id' => intval($branch['ID']),
                        'name' => $branch['NAME'],
                        'phones' => $branch['PHONES']['VALUE'],
                        'address' => $branch['ADDRESS']['VALUE'],
                        'location' => $branch['LOCATION']['VALUE'],
                    ];
                    $result[] = $branch;
                }
//                while ($arSect = $rsSect->Fetch())
//                {
//                    $item = [
//                        "id"   => intval($arSect["ID"]),
//                        "name" => $arSect["NAME"],
//                        //"depth" => intval($arSect["DEPTH_LEVEL"])
//                        //"count" => intval($arSect["ELEMENT_CNT"]),
//                    ];
//                    if($arSect["PICTURE"] > 0)
//                        $item["icon"] = $arSect["PICTURE"] > 0 ? cGeneral::getFullPath(
//                            CFile::getPath($arSect["PICTURE"])) : null;
//                    /*if(intval($arSect["IBLOCK_SECTION_ID"])>0)
//                        $item["parent_id"] = intval($arSect["IBLOCK_SECTION_ID"]);*/
//                    $result[] = $item;
//                }
                /*if(count($result)==0) {
                    $error = Errors::NO_DATA;
                }*/
            } else {
                $error = cErrors::MODULE_IBLOCK_NOT_LOADED;
            }
        } catch (Exception $e) {
            $error_message = $e->getMessage();
        }
        return ['result' => $result, 'error' => $error, 'error_message' => $error_message];
    }

    private static function getItemsList($iblock_id, $section_id,$include_subsection=false,$sort=["by"=>"price","dir"=>"asc"], $page=1, $count_by_page=20)
    {
        $error = false;
        $error_message = false;
        $result = [];
        try {
            if (Loader::includeModule("iblock")) {
                $arSelect = array(
                    "IBLOCK_ID",
                    "ID",
                    "NAME",
                    "PREVIEW_PICTURE",
                    "IBLOCK_SECTION_ID"
                    //"PRICE_1"
                );
                $arFilter = array(
                    "IBLOCK_ID" => $iblock_id,

                    //"ACTIVE_DATE" => "Y",
                    "ACTIVE" => "Y",
                    "=AVAILABLE"=>"Y",//Если не нужно выводить товары под заказ
                    ">PROPERTY_MINIMUM_PRICE" => 0, //Если проблем с заполнением минимальной цены нет (при включении режима совместимости)
                    //Проверка указания цены, при наличии торговых предложений будет заполнено св-во PROPERTY_MINIMUM_PRICE иначе PRICE_1
                    /*array(
                        "LOGIC" => "OR",
                        ">PROPERTY_MINIMUM_PRICE" => 0,
                        ">PRICE_1" => 0
                    )*/
                );
                if($section_id>0)
                  $arFilter["SECTION_ID"] = $section_id;

                //Включение элементов из подразделов в выдачу
                $arFilter["INCLUDE_SUBSECTIONS"] = $include_subsection?"Y":"N";

                //Настройки пагинации
                if ($page >= 1) {
                    $arPagination = [];
                    $arPagination["iNumPage"] = $page;
                    $arPagination["checkOutOfRange"] = true;
                    $arPagination["bShowAll"] = false;
                    $arPagination["nPageSize"] = $count_by_page;
                } else {
                    $arPagination = array("nTopCount" => $count_by_page);
                }
                //Сортировка
                $sort["dir"] = $sort["dir"]=="desc"?"desc":"asc";
                if(!empty($sort["by"]))
                {
                    $arSort = [$sort["by"]=>$sort["dir"]];
                }else{
                    $arSort = ["by"=>"price","dir"=>"asc"];
                }
                //array("sort" => "ASC", "name" => "ASC")
                $res = CIBlockElement::GetList($arSort, $arFilter, false, $arPagination, $arSelect);

                while ($ob = $res->GetNextElement()) {
                    $buffer = [];
                    $arFields = $ob->GetFields();
                    $arProps = $ob->GetProperties();

                    $buffer["id"] = (int)$arFields["ID"];
                    $buffer["name"] = trim($arFields["NAME"]);
                    $buffer["category_id"] = (int)$arFields["IBLOCK_SECTION_ID"];
                    //$buffer["slides"]  = $arProps["PROPERTIES"]["MORE_PHOTO"];
                    $buffer["slides"]  = [cGeneral::getFullPath(CFile::GetPath($arFields['PREVIEW_PICTURE']))];
                    $buffer["brand"]   = $arProps["BREND"]["VALUE"]; // бренд
                    $buffer["min_price"]   = floatval($arProps['MINIMUM_PRICE']["VALUE"]);  //основная цена (форматированная цена с валютой, цена отдельно в виде числа)
                    //$buffer["price"]    = floatval($arFields['PRICE_1']);  //цена товара, если нет торговых предложений
                    //$buffer["currency"]       = 'RUB'; //валюта

                    $result[] = $buffer;
                    unset($arProps);
                    unset($arFields);
                }
                if(empty($result)){
                    $result =[];
                }
            } else {
                $error = cErrors::MODULE_IBLOCK_NOT_LOADED;
            }
        } catch (Exception $e) {
            $error_message = $e->getMessage();
        }
        return ['result' => $result, 'error' => $error, 'error_message' => $error_message];
    }

    private static function getItemDetail($iblock_id, $id)
    {
        $error = false;
        $error_message = false;
        $result = [];
        try {
            if (Loader::includeModule("iblock")) {
                /*$action = function () {
                    return General::getItemsListByType("stock")["shops"];
                };
                $newslist = self::cachedataD7('NewsList', $action, 'NewsList', 86400, $iblock_id);*/
                $arSelect = array(
                    "IBLOCK_ID",
                    "ID",
                    "NAME",
                    "PREVIEW_PICTURE",
                    "PREVIEW_TEXT",
                    "DETAIL_TEXT",
                    'WEIGHT', 'WIDTH', 'HEIGHT', 'LENGTH',
                    "PRICE_1",
                    "PRICE_8",
                );
                $arFilter = array(
                    "IBLOCK_ID" => $iblock_id,
                    //"SECTION_ID" => $section_id,
                    "ID" => $id,
                    "ACTIVE_DATE" => "Y",
                    "ACTIVE" => "Y"
                );
                $res = CIBlockElement::GetList(array("sort" => "ASC", "name" => "ASC"), $arFilter, false, array(), $arSelect);

                while ($ob = $res->GetNextElement()) {
                    $arFields = $ob->GetFields();
                    $buffer["id"] = intval($arFields["ID"]);
                    //$buffer["sort"] = $arFields["SORT"];
                    $buffer["name"] = trim($arFields["NAME"]);
                    $arProps = $ob->GetProperties(["sort"=>"asc"],['ACTIVE'=>"Y", "EMPTY"=>"N"]);
                    $buffer["picture_url"] = cGeneral::getFullPath(CFile::GetFileArray($arFields['PREVIEW_PICTURE'])["SRC"]);
                    //$buffer["description"] = General::prepare_slashes($arFields["PREVIEW_TEXT"]);

                    $buffer["content"] = cGeneral::prepare_slashes($arFields["DETAIL_TEXT"]);
                    //$buffer["props_tmp"] = $arProps;
                    foreach ($arProps as $arProp) {
                        if($arProp["CODE"] == "CML2_BAR_CODE")
                            $arFields["CML2_BAR_CODE"] = $arProp["VALUE"];
                        if(in_array($arProp["PROPERTY_TYPE"],self::VALID_PROP_TYPE) && !in_array($arProp["CODE"], self::EXCLUDED_PROPERTIES)) {
                            $val =  $arProp["VALUE"];
                            if($arProp["PROPERTY_TYPE"]=="N")
                            {
                                $val = floatval($val);
                            }
                            $buffer["props"][$arProp["ID"]] = ["id"=>intval($arProp["ID"]),"name"=> $arProp["NAME"],"code"=>$arProp["CODE"], "value" => $val];
                        }
                    }
                    unset($arProps);
                    $buffer["delivery_params"] =
                        [
                            "weight"=>floatval($arFields["WEIGHT"]),
                            "width"=>floatval($arFields["WIDTH"]),
                            "height"=>floatval($arFields["HEIGHT"]),
                            "length"=>intval($arFields["LENGTH"])
                        ];
                    // Получение торговых предложений
                    $res_offers = CCatalogSKU::getOffersList(
                        $arFields["ID"],
                        0,//TODO: Иcправить на кэшированное значение
                        $skuFilter = array("ACTIVE"=>"Y",'=AVAILABLE' => 'Y','>PRICE' => 0, '@PRICE_TYPE' => [1,8]),
                        $fields = array(
                            "ID",
                            "NAME",
                            "PREVIEW_PICTURE",
                            "PRICE_1",
                            "PRICE_8",

                            //'WEIGHT', 'WIDTH', 'HEIGHT', 'LENGTH'
                        ),
                        $propertyFilter = array("CODE"=>array('TSVET_1', 'OBEM_VNUTRENNEY_PAMYATI', 'OBEM_OPERATIVNOY_PAMYATI','MORE_PHOTO','CML2_BAR_CODE'))
                    );
                    $isFoundOffers = count($res_offers[$arFields["ID"]])>0;
                    if($isFoundOffers)
                    {
                        $buffer["price"] = 0;
                        $buffer["articul"] = "";
                    }else{
                        $buffer["price"]   = floatval($arFields["PRICE_1"]);
                        $buffer["articul"] = $arFields["CML2_BAR_CODE"];
                    }

                    //Список торговых предложений
                    $buffer["offers"] = [];
                    if($isFoundOffers) {
                        foreach ($res_offers[$arFields["ID"]] as $offer_id => $offer_data) {
                            $offer_props = [];
                            $slides = [];
                            if(count($offer_data["PROPERTIES"]['MORE_PHOTO']['VALUE'])>0) {
                                foreach ($offer_data["PROPERTIES"]['MORE_PHOTO']['VALUE'] as $photo) {
                                    if ($offer_data["PROPERTIES"]['MORE_PHOTO']["PROPERTY_TYPE"] == "F") {
                                        $slides[] = cGeneral::getFullPath(
                                            CFile::GetFileArray($photo)["SRC"]
                                        );
                                    } else {
                                        $slides[] = $photo;
                                    }
                                }
                                unset($offer_data["PROPERTIES"]['MORE_PHOTO']);
                            }
                            $offer_data['CML2_BAR_CODE'] = $offer_data["PROPERTIES"]['CML2_BAR_CODE'];
                            unset($offer_data["PROPERTIES"]['CML2_BAR_CODE']);
                            if(count($offer_data["PROPERTIES"])>0)
                            {
                                foreach ($offer_data["PROPERTIES"] as $offer_props_key => $offer_props_tmp)
                                {
                                    if(is_array($offer_props_tmp["VALUE"]))
                                    {
                                        $vals = $offer_props_tmp["VALUE"];
                                    }else
                                        $vals =
                                        [
                                            "_code"  => $offer_props_key,
                                            "id"    => floatval($offer_props_tmp["VALUE_ENUM_ID"]),
                                            "value" => $offer_props_tmp["VALUE"],
                                            //"xml_id" => $offer_props_tmp["VALUE_XML_ID"]
                                        ];

                                    $offer_props[floatval($offer_props_tmp["ID"])] = $vals;
                                }
                            }
                            $picture_url = cGeneral::getFullPath(CFile::GetFileArray($offer_data["PREVIEW_PICTURE"])["SRC"]);
                            //Добавить картинку в список фото в карточке.
                            $slides[]= $picture_url;
                            $offer = [
                                "name"          => trim($offer_data["NAME"]),
                                "picture_url"   => $picture_url,
                                "price"         => floatval($offer_data["PRICE_1"]),
                                "articul"       => $offer_data["CML2_BAR_CODE"]["VALUE"],
                                "slides"        => $slides,
                                /*"params"=>
                                    [
                                        "weight"=>$offer_data["WEIGHT"],
                                        "width"=>$offer_data["WIDTH"],
                                        "height"=>$offer_data["HEIGHT"],
                                        "length"=>$offer_data["LENGTH"],
                                    ],*/
                                //"price_action"=> $offer_data["PRICE_8"],
                                "props"=> $offer_props
                            ];


                            $buffer["offers"][floatval($offer_id)] = $offer;
                        }
                    }


                    // $buffer["sale"] = $newslist[$arFields["ID"]] ? 1 : 0;

                    $result[] = $buffer;
                    unset($arProps);
                    unset($arFields);
                    break;
                }
                if (empty($result)) {
                    //$result = [];
                    $error = cErrors::NOT_EXIST_ID;
                }
            } else {
                $error = cErrors::MODULE_IBLOCK_NOT_LOADED;
            }
        } catch (Exception $e) {
            $error_message = $e->getMessage();
        }
        return ['result' => $result, 'error' => $error, 'error_message' => $error_message];
    }

	private static function decodeName($name)
	{
		return html_entity_decode($name);
	}


}