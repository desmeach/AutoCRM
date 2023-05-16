<?php
/**
 * Created: 18.03.2023, 16:59
 * Author : Vladislav Naryzhny <desmeach@gmail.com>
 * Company: 34web Studio
 */

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();
/** @var array $arCurrentValues */

$arComponentParameters = array(
    "GROUPS" => array(),
    "PARAMETERS" => array(
        "ENTITY" => array(
            "PARENT" => "BASE",
            "NAME" => "Сущность",
            "TYPE" => "LIST",
            "ADDITIONAL_VALUES" => "Y",
            "VALUES" => [
                'clients' => 'clients',
                'cars' => 'cars',
                'orders' => 'orders',
                'products' => 'products',
                'branches' => 'branches',
                'masters' => 'masters',
                'managers' => 'managers'
            ],
            "REFRESH" => "Y",
        ),
    ),
);