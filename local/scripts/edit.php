<?php
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");
CModule::IncludeModule('iblock');
require_once $_SERVER['DOCUMENT_ROOT'] . '/local/scripts/functions.php';

global $USER;
$IBlOCK_ID = $_POST['IBLOCK_ID'];
$NAME = $_POST['NAME'];
$ID = $_POST['ID'];
unset($_POST['IBLOCK_ID'], $_POST['NAME'], $_POST['ID']);
$PROPS = getFormProps($_POST);

$el = new CIBlockElement;

$arFields = [
    'MODIFIED_BY' => $USER->GetID(),
    'NAME' => $NAME,
    'PROPERTY_VALUES' => $PROPS
];

header('Content-Type: json/application');
if ($ID = $el->Update($ID, $arFields, false, false))
    echo json_encode(['success' => $ID]);
else
    echo json_encode(['error' => $el->LAST_ERROR]);


