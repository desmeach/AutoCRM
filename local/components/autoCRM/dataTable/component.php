<?php
/**
 * Created: 18.03.2023, 16:50
 * Author : Vladislav Naryzhny <desmeach@gmail.com>
 * Company: 34web Studio
 */

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();
/** @var array $arParams */
CModule::IncludeModule('iblock');

const COLUMNS_ORDER_DEFS = [
    'clients' => [0 => 'asc'],
    'orders' => [3 => 'asc', 0 => 'asc'],
    'products' => [0 => 'asc'],
    'branches' => [0 => 'asc'],
    'masters' => [0 => 'asc'],
    'managers' => [0 => 'asc'],
];
const COLUMN_DEFS = [
    'clients' => [
        ['title' => 'Пол', 'data' => 'gender', 'width' => '5%'],
        ['title' => 'Телефон', 'data' => 'phone', 'width' => '15%'],
        ['title' => 'Автомобили', 'data' => 'cars', 'width' => '20%'],
        ['title' => 'Email', 'data' => 'email', 'width' => '20%'],
    ],
    'orders' => [
        ['title' => 'Клиент', 'data' => 'client', 'width' => '3%'],
        ['title' => 'Автомобиль', 'data' => 'car', 'width' => '1%'],
        ['title' => 'Статус', 'data' => 'status', 'width' => '10%'],
        ['title' => 'Получена', 'data' => 'date_receive'],
        ['title' => 'Прием', 'data' => 'date_accept'],
        ['title' => 'Начата', 'data' => 'date_start'],
        ['title' => 'Завершена', 'data' => 'date_end'],
        ['title' => 'Услуги', 'data' => 'products', 'width' => '10%'],
        ['title' => 'Стоимость', 'data' => 'total_price'],
    ],
    'products' => [
        ['title' => 'Цена', 'data' => 'price', 'width' => '5%'],
        ['title' => 'Нормо-час', 'data' => 'working_hour', 'width' => '10%'],
        ['title' => 'Автосервис', 'data' => 'branches', 'width' => '15%'],
    ],
    'branches' => [
        ['title' => 'Телефоны', 'data' => 'phones', 'width' => '15%'],
        ['title' => 'Адрес', 'data' => 'address', 'width' => '20%'],
        ['title' => 'Координаты', 'data' => 'location', 'width' => '20%'],
    ],
    'masters' => [
        ['title' => 'Автосервис', 'data' => 'branch', 'width' => '50%'],
    ],
    'managers' => [
        ['title' => 'Email', 'data' => 'email', 'width' => '50%'],
    ],
];
const ENTITY_IBLOCK_ID = [
    'clients' => 1,
    'cars' => 2,
    'orders' => 3,
    'products' => 4,
    'branches' => 5,
    'masters' => 7,
];
$arResult['ENTITY'] = $arParams['ENTITY'];
$IBLOCK_ID = ENTITY_IBLOCK_ID[$arParams['ENTITY']];
if ($IBLOCK_ID)
    $propsList = CIBlockProperty::GetList(['SORT' => 'ASC'], ['IBLOCK_ID' => $IBLOCK_ID, 'ACTIVE' => 'Y']);
$arResult['COLUMN_DEFS'][] = ['title' => 'ID', 'data' => 'id', 'width' => '1%'];
switch ($arResult['ENTITY']) {
    case 'clients':
    case 'masters':
    case 'managers':
        $arResult['COLUMN_DEFS'][] = ['title' => 'ФИО', 'data' => 'name'];
        break;
    case 'cars':
        $arResult['COLUMN_DEFS'][] = ['title' => 'VIN', 'data' => 'name'];
        break;
    case 'products':
        $arResult['COLUMN_DEFS'][] = ['title' => 'Наименование', 'data' => 'name', 'width' => '20%'];
        break;
    case 'branches':
        $arResult['COLUMN_DEFS'][] = ['title' => 'Название', 'data' => 'name'];
        break;
}
$arResult['COLUMN_DEFS'] = array_merge(
    $arResult['COLUMN_DEFS'],
    COLUMN_DEFS[$arParams['ENTITY']],
);
$arResult['COLUMN_DEFS'][] = ['title' => 'Действия', 'data' => 'actions', 'width' => '5%'];
$arResult['COLUMN_ORDER_DEFS'] = COLUMNS_ORDER_DEFS[$arParams['ENTITY']];
$this->IncludeComponentTemplate();
