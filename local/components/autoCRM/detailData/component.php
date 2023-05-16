<?php
/**
 * Created: 18.03.2023, 16:50
 * Author : Vladislav Naryzhny <desmeach@gmail.com>
 * Company: 34web Studio
 */

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();
CModule::IncludeModule('iblock');

/** @var array $arParams */
if (!isset($_GET['ID']) || !is_numeric($_GET['ID']))
    die('Элемент не найден');
$arResult['IBLOCK_ID'] = $arParams['IBLOCK_ID'];
$ID = $_GET['ID'];
switch ($arResult['IBLOCK_ID']) {
    case 1:
        $arResult['ELEMENT'] = \lib\Controllers\ClientsController::getByID($ID);
        break;
    case 2:
        $arResult['ELEMENT'] = \lib\Controllers\CarsController::getByID($ID);
        break;
    case 3:
        $arResult['ELEMENT'] = \lib\Controllers\OrdersController::getByID($ID);
        break;
    case 4:
        $arResult['ELEMENT'] = \lib\Controllers\ProductsController::getByID($ID);
        break;
    case 5:
        $arResult['ELEMENT'] = \lib\Controllers\BranchesController::getByID($ID);
        break;
    case 7:
        $arResult['ELEMENT'] = \lib\Controllers\MastersController::getByID($ID);
        break;
}
$propsList = CIBlockProperty::GetList(false, ['IBLOCK_ID' => $arResult['IBLOCK_ID'], 'ACTIVE' => 'Y']);
$i = 0;
$arResult['PROPS'] = [];
switch ($arResult['IBLOCK_ID']) {
    case 1:
    case 7:
        $arResult['NAME_FIELD'] = 'ФИО';
        break;
    case 2:
        $arResult['NAME_FIELD'] = 'VIN';
        break;
    case 4:
        $arResult['NAME_FIELD'] = 'Наименование';
        break;
    case 5:
        $arResult['NAME_FIELD'] = 'Название';
        break;
}
while ($prop = $propsList->GetNext()) {
    $arResult['PROPS'][$i] = $prop;
    if (is_array($arResult['ELEMENT'][$prop['CODE']]['VALUE'])) {
        $names = [];
        if (isset($arResult['ELEMENT'][$prop['CODE']]['VALUE']['LAST_NAME']))
            $arResult['ELEMENT'][$prop['CODE']]['VALUE'] =
                $arResult['ELEMENT'][$prop['CODE']]['VALUE']['LAST_NAME'] . " " .
                $arResult['ELEMENT'][$prop['CODE']]['VALUE']['NAME'];
        elseif (isset($arResult['ELEMENT'][$prop['CODE']]['VALUE']['NAME']))
            $arResult['ELEMENT'][$prop['CODE']]['VALUE'] = $arResult['ELEMENT'][$prop['CODE']]['VALUE']['NAME'];
        else {
            foreach ($arResult['ELEMENT'][$prop['CODE']]['VALUE'] as $value) {
                $names[] = $value['NAME'] ?? $value;
            }
            $arResult['ELEMENT'][$prop['CODE']]['VALUE'] = implode(", ", $names);
        }
    }
    if ($prop['PROPERTY_TYPE'] == 'L') {
        $property_list = CIBlockPropertyEnum::GetList(Array('DEF'=>'DESC', 'SORT'=>'ASC'),
            Array('IBLOCK_ID'=>$arResult['IBLOCK_ID'], 'CODE'=>$prop['CODE']));
        while ($list_fields = $property_list->GetNext()) {
            $arResult['PROPS'][$i]['VALUES'][$list_fields['ID']] = $list_fields['VALUE'];
        }
    }
    elseif ($prop['PROPERTY_TYPE'] == 'E') {
        $property_enum = CIBlockElement::GetList(Array('DEF'=>'DESC', 'SORT'=>'ASC'),
            Array('IBLOCK_ID'=>$prop['LINK_IBLOCK_ID']));
        while ($enum_fields = $property_enum->GetNext()) {
            $arResult['PROPS'][$i]['VALUES'][$enum_fields['ID']] = $enum_fields['NAME'];
        }
    }
    $i++;
}
$this->IncludeComponentTemplate();
