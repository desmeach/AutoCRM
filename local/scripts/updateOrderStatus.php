<?php
global $USER;
define('STOP_STATISTICS', true);
require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_before.php');
$GLOBALS['APPLICATION']->RestartBuffer();
CModule::IncludeModule('iblock');
$id = $_POST['ID'];
$status = $_POST['STATUS'];
$statuses = [
    3 => 'Новая',
    4 => 'Отклонена',
    5 => 'Запланирована',
    6 => 'В работе',
    7 => 'Рекламация',
    8 => 'Завершена',
];
try {
    $order = CIBlockElement::GetList(false, ['IBLOCK_ID' => 3, 'ID' => $id])->GetNextElement();
    $orderProps = $order->GetProperties();
    $elem = new CIBlockElement();
    CIBlockElement::SetPropertyValuesEx($id, false, array('STATUS' => $status));
    if ($status == 3 || $status == 4) {
        CIBlockElement::SetPropertyValuesEx($id, 3, array('MANAGER' => ''));
        CIBlockElement::SetPropertyValuesEx($id, 3, array('MASTER' => ''));
        CIBlockElement::SetPropertyValuesEx($id, 3, array('DATE_RECEIVE' => FormatDate("d.m.Y H:i:s")));
        CIBlockElement::SetPropertyValuesEx($id, 3, array('DATE_ACCEPT' => ''));
        CIBlockElement::SetPropertyValuesEx($id, 3, array('DATE_START' => ''));
        CIBlockElement::SetPropertyValuesEx($id, 3, array('DATE_END' => ''));
        CIBlockElement::SetPropertyValuesEx($id, 3, array('COMMENT_MANAGER' => ''));
        CIBlockElement::SetPropertyValuesEx($id, 3, array('COMMENT_MASTER' => ''));

    }
    if ($status == 5) {
        $manager = $USER->GetID();
        CIBlockElement::SetPropertyValuesEx($id, 3, array('MANAGER' => $manager));
        CIBlockElement::SetPropertyValuesEx($id, 3, array('MASTER' => $_POST['MASTER']));
        CIBlockElement::SetPropertyValuesEx($id, 3, array('DATE_RECEIVE' => FormatDate("d.m.Y H:i:s")));
        CIBlockElement::SetPropertyValuesEx($id, 3, array('DATE_ACCEPT' => FormatDate("d.m.Y H:i:s")));
        CIBlockElement::SetPropertyValuesEx($id, 3, array('DATE_START' => ''));
        CIBlockElement::SetPropertyValuesEx($id, 3, array('DATE_END' => ''));
        CIBlockElement::SetPropertyValuesEx($id, 3, array('COMMENT_MANAGER' => ''));
        CIBlockElement::SetPropertyValuesEx($id, 3, array('COMMENT_MASTER' => ''));

    }
    if ($status == 6) {
//        if ($_POST['PRODUCTS'])
//        $products = $_POST['PRODUCTS'];
        $manager = $USER->GetID();
        CIBlockElement::SetPropertyValuesEx($id, 3, array('DATE_RECEIVE' => FormatDate("d.m.Y H:i:s")));
        CIBlockElement::SetPropertyValuesEx($id, 3, array('DATE_ACCEPT' => FormatDate("d.m.Y H:i:s")));
        CIBlockElement::SetPropertyValuesEx($id, 3, array('DATE_START' => FormatDate("d.m.Y H:i:s")));
        CIBlockElement::SetPropertyValuesEx($id, 3, array('DATE_END' => ''));
        CIBlockElement::SetPropertyValuesEx($id, 3, array('COMMENT_MANAGER' => ''));
        CIBlockElement::SetPropertyValuesEx($id, 3, array('COMMENT_MASTER' => ''));

    }
    if ($status == 7) {
        $manager = $USER->GetID();
        CIBlockElement::SetPropertyValuesEx($id, 3, array('DATE_RECEIVE' => FormatDate("d.m.Y H:i:s")));
        CIBlockElement::SetPropertyValuesEx($id, 3, array('DATE_ACCEPT' => FormatDate("d.m.Y H:i:s")));
        CIBlockElement::SetPropertyValuesEx($id, 3, array('DATE_START' => FormatDate("d.m.Y H:i:s")));
        CIBlockElement::SetPropertyValuesEx($id, 3, array('DATE_END' => FormatDate("d.m.Y H:i:s")));
        CIBlockElement::SetPropertyValuesEx($id, 3, array('COMMENT_MANAGER' => ''));
        CIBlockElement::SetPropertyValuesEx($id, 3, array('COMMENT_MASTER' => ''));

    }
}
catch (Exception $ex) {
    die($ex->getMessage());
}

