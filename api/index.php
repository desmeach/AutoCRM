<?php
require_once($_SERVER['DOCUMENT_ROOT'] . "/bitrix/modules/main/include/prolog_before.php");
if (\Bitrix\Main\Loader::includeModule('s34web.mobile.api')) {
    $api = new s34web\Mobile\Api\Init();
    $api->start();
}else{
    echo "Модуль не установлен";
}