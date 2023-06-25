<?php
/**
 * Created: 18.03.2023, 16:50
 * Author : Vladislav Naryzhny <desmeach@gmail.com>
 * Company: 34web Studio
 */

use autocrm_tables\lib\controllers\Controller;
use autocrm_tables\lib\controllers\ControllersHandler;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();
CModule::IncludeModule('iblock');

/** @var array $arParams */
if (!isset($_GET['ID']) || !is_numeric($_GET['ID']))
    die('Элемент не найден');
$arResult['ENTITY'] = $arParams['ENTITY'];
$ID = $_GET['ID'];
/** @var Controller $controller */
$controller = ControllersHandler::handleController($arParams['ENTITY']);
if (isset($controller['error']))
    die($controller['error']);
$arResult['ELEMENT'] = $controller::getById($ID);
if (!$arResult['ELEMENT'])
    die('Элемент не найден');
$arResult['PROPS'] = $controller::getProps();
$this->IncludeComponentTemplate();
