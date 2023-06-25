<?php
/**
 * Created: 18.03.2023, 16:50
 * Author : Vladislav Naryzhny <desmeach@gmail.com>
 * Company: 34web Studio
 */

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();
CModule::IncludeModule('iblock');

use lib\Controllers\ControllerHandler;
use lib\Controllers\ManagersController;

/** @var array $arParams */
const ORDER_ADD_PROPS = ['CLIENT', 'CAR', 'PRODUCTS', 'KEY'];
const ENTITY_IBLOCK_ID = [
    'clients' => 1,
    'cars' => 2,
    'orders' => 3,
    'products' => 4,
    'branches' => 5,
    'masters' => 7,
];

$arResult['KEY'] = getKey();
$IBLOCK_ID = ENTITY_IBLOCK_ID[$arParams['ENTITY']];
$arResult['ACTION'] = $arParams['ACTION'];
$arResult['ENTITY'] = $arParams['ENTITY'];
if (isset($_GET['ID'])) {
    $controller = ControllerHandler::getController($arResult['ENTITY']);
    $arResult['ELEMENT'] = $controller::getByID($_GET['ID']);
}
$propsList = CIBlockProperty::GetList([], ['IBLOCK_ID' => $IBLOCK_ID, 'ACTIVE' => 'Y']);
$i = 0;
$arResult['PROPS'] = [];
while ($prop = $propsList->GetNext()) {
    if ($arResult['ACTION'] == 'add' &&
        $arResult['ENTITY'] == 'orders' &&
        !in_array($prop['CODE'], ORDER_ADD_PROPS))
        continue;
    $arResult['PROPS'][$i] = $prop;
    if ($prop['PROPERTY_TYPE'] == 'L') {
        $property_list = CIBlockPropertyEnum::GetList(Array('DEF'=>'DESC', 'SORT'=>'ASC'),
            Array('IBLOCK_ID'=>$IBLOCK_ID, 'CODE'=>$prop['CODE']));
        while ($list_fields = $property_list->GetNext()) {
            $arResult['PROPS'][$i]['VALUES'][$list_fields['ID']] = $list_fields['VALUE'];
        }
    }
    elseif ($prop['PROPERTY_TYPE'] == 'E' || $prop['CODE'] == 'MANAGER') {
        if ($prop['CODE'] == 'MANAGER') {
            $elements = ManagersController::getList();
            foreach ($elements as $element) {
                $arResult['PROPS'][$i]['VALUES'][$element['ID']] = $element['LAST_NAME'] . " " . $element['NAME'];
            }
        }
        else {
            $property_enum = CIBlockElement::GetList(Array('DEF'=>'DESC', 'SORT'=>'ASC'),
                Array('IBLOCK_ID'=>$prop['LINK_IBLOCK_ID'], 'PROPERTY_KEY' => getKey()));
            while ($enum_fields = $property_enum->GetNext()) {
                $arResult['PROPS'][$i]['VALUES'][$enum_fields['ID']] = $enum_fields['NAME'];
            }
        }
    }
    $i++;
}
$this->IncludeComponentTemplate();
