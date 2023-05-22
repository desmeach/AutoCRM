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

AddEventHandler("main", "OnAfterUserAdd", "OnAfterUserAddHandler");
function OnAfterUserAddHandler(&$arFields)
{
    if($arFields["ID"] > 0) {
        \lib\Controllers\BranchesController::add(['NAME' => $arFields['UF_CARSERVICE'],
            'KEY' => $arFields['UF_KEY']]);
    }
}