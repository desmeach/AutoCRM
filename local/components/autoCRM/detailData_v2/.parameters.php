<?php
/**
 * Created: 20.04.2023, 14:51
 * Author : Vladislav Naryzhny <desmeach@gmail.com>
 * Company: 34web Studio
 */

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();
/** @var array $arCurrentValues */
CModule::IncludeModule("iblock");

$arIBlockType = CIBlockParameters::GetIBlockTypes();

$entities = [
    'clients' => 'Клиенты',
    'carservices' => 'Автосервисы',
];

$arComponentParameters = array(
    "GROUPS" => array(),
    "PARAMETERS" => array(
        "ENTITY" => array(
            "PARENT" => "BASE",
            "NAME" => "Сущность",
            "TYPE" => "LIST",
            "ADDITIONAL_VALUES" => "N",
            "VALUES" => $entities,
            "REFRESH" => "Y",
        ),
    ),
);