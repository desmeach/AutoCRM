<?php
/**
 * Created: 08.03.2023, 18:50
 * Author : Vladislav Naryzhny <desmeach@gmail.com>
 * Company: 34web Studio
 */

use Bitrix\Main\Data\Cache;
use Bitrix\Main\Engine\CurrentUser;

require_once($_SERVER['DOCUMENT_ROOT'] . '/local/php_interface/autoload.php');

function debug(...$values): void {
    foreach ($values as $value)
        print_r('<pre>' . htmlspecialchars(print_r($value, true)) . '</pre>');
}
function setLog($value, $desc = ''): void {
    if ($desc != '')
        $log = date('Y-m-d H:i:s') . " $desc: " . print_r($value, true);
    else
        $log = date('Y-m-d H:i:s') . " " . print_r($value, true);
    file_put_contents($_SERVER['DOCUMENT_ROOT'] . '/logs/log.txt', $log . PHP_EOL, FILE_APPEND);
}
function getKey() {
    $userID = CurrentUser::get()->getId();
    $rsUser = CUser::GetByID($userID);
    $arUser = $rsUser->Fetch();
    return $arUser['UF_KEY'];
}

AddEventHandler("main", "OnBeforeUserRegister", "OnBeforeUserRegisterHandler");
function OnBeforeUserRegisterHandler(&$arFields)
{
    $arFields['PERSONAL_BIRTHDAY'] = FormatDate('d.m.Y H:i:s', MakeTimeStamp($arFields['PERSONAL_BIRTHDAY']));
}

AddEventHandler("main", "OnAfterUserAdd", "OnAfterUserAddHandler");
function OnAfterUserAddHandler(&$arFields)
{
    if($arFields["ID"] > 0) {
        \lib\Controllers\BranchesController::add(['NAME' => $arFields['UF_CARSERVICE'],
            'KEY' => $arFields['UF_KEY']]);
    }
}

AddEventHandler("iblock", "OnBeforeIBlockElementUpdate", "calculateTotalSum");
AddEventHandler("iblock", "OnBeforeIBlockElementAdd", "calculateTotalSum");
AddEventHandler("iblock", "OnBeforeIBlockElementSetPropertyValuesEx", "calculateTotalSumEx");
function calculateTotalSumEx(int $ELEMENT_ID, int $IBLOCK_ID, array &$PROPERTY_VALUES, array $FLAGS) {
    if ($IBLOCK_ID != 3)
        return;
    $products = CIBlockElement::GetList(false, ['IBLOCK_ID' => 4, 'ID' => $PROPERTY_VALUES['PRODUCTS']],
        false, false, ['PROPERTY_PRICE']);
    $totalSum = 0;
    $orderPrices = [];
    while ($product = $products->GetNext()) {
        $orderPrices[] = $product['PROPERTY_PRICE_VALUE'];
    }
    foreach ($orderPrices as $price) {
        $totalSum += $price;
    }
    $PROPERTY_VALUES['TOTAL_PRICE'] = $totalSum;
}
function calculateTotalSum(&$arFields) {
    if ($arFields['IBLOCK_ID'] != 3)
        return;
    $products = CIBlockElement::GetList(false, ['IBLOCK_ID' => 4, 'ID' => $arFields['PROPERTY_VALUES']['PRODUCTS']],
        false, false, ['PROPERTY_PRICE']);
    $totalSum = 0;
    $orderPrices = [];
    while ($product = $products->GetNext()) {
        $orderPrices[] = $product['PROPERTY_PRICE_VALUE'];
    }
    foreach ($orderPrices as $price) {
        $totalSum += $price;
    }
    $arFields['PROPERTY_VALUES']['TOTAL_PRICE'] = $totalSum;
}