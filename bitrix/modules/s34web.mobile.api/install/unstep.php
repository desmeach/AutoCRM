<?php

use Bitrix\Main\Localization\Loc;
/*
//Удаление HL MobileUserAuthData
use Bitrix\Main\Loader;
Loader::IncludeModule('highloadblock');
use Bitrix\Highloadblock as HL;

$result = \Bitrix\Highloadblock\HighloadBlockTable::getList(array('filter'=>array('=TABLE_NAME'=>"b_hlbd_mobile_user_auth_data")));
if($row = $result->fetch())
{
  $result = HL\HighloadBlockTable::delete($row["ID"]);
}
*/
echo CAdminMessage::ShowNote(Loc::getMessage('API_UNINSTALL_MESSAGE'));