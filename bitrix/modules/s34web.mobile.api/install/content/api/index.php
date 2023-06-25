<?php
/**
 * Created: 05.11.2020, 12:53
 * Author : Alex Rilkov <alex@34web.ru>
 * Company: 34web Studio
 */

use Bitrix\Main\Loader;

require_once($_SERVER['DOCUMENT_ROOT'] . "/bitrix/modules/main/include/prolog_before.php");
if (Loader::includeModule('s34web.mobile.api')) {

    $api = new s34web\Mobile\Api\Init();
    $api->start();

} else {
    echo "Модуль не установлен";
}