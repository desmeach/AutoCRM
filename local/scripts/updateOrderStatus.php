<?php
require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_before.php');
CModule::IncludeModule('iblock');

global $USER;
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
    $manager = $USER->GetID();
    switch ($status) {
        case 3:
            CIBlockElement::SetPropertyValuesEx($id, 3, [
                'STATUS' => $status,
                'MANAGER' => '',
                'MASTER' => '',
                'DATE_RECEIVE' => FormatDate("d.m.Y H:i:s"),
                'MILEAGE' => '',
                'DATE_ACCEPT' => '',
                'DATE_START' => '',
                'DATE_END' => '',
                'COMMENT_MASTER' => '',
                'COMMENT_MANAGER' => '',
            ]);
            break;
        case 4:
            CIBlockElement::SetPropertyValuesEx($id, 3, [
                'STATUS' => $status,
                'MANAGER' => $manager,
                'MASTER' => '',
                'DATE_RECEIVE' => FormatDate("d.m.Y H:i:s"),
                'MILEAGE' => '',
                'DATE_ACCEPT' => '',
                'DATE_START' => '',
                'DATE_END' => '',
                'COMMENT_MASTER' => '',
                'COMMENT_MANAGER' => $_POST['MANAGER_COMMENT'] ?? '',
            ]);
            break;
        case 5:
            CIBlockElement::SetPropertyValuesEx($id, 3, [
                'STATUS' => $status,
                'MANAGER' => $manager,
                'MASTER' => '',
                'DATE_ACCEPT' => FormatDate("d.m.Y H:i:s"),
                'DATE_START' => '',
                'DATE_END' => '',
                'MILEAGE' => '',
                'COMMENT_MASTER' => '',
                'COMMENT_MANAGER' => ''
            ]);
            break;
        case 6:
            CIBlockElement::SetPropertyValuesEx($id, 3, [
                'STATUS' => $status,
                'MANAGER' => $manager,
                'MASTER' => $_POST['MASTER'],
                'DATE_START' => FormatDate("d.m.Y H:i:s"),
            ]);
            break;
        case 8:
            $orderList = CIBlockElement::GetList(false, ['IBLOCK_ID' => 3, 'ID' => $id],
                false, false, ['PROPERTY_PRODUCTS', 'PROPERTY_CAR']);
            $orderProducts = [];
            $carID = 0;
            while ($order = $orderList->GetNext()) {
                $orderProducts[] = $order['PROPERTY_PRODUCTS_VALUE'];
                $carID = $order['PROPERTY_MILEAGE_VALUE'];
            }
            foreach ($_POST['PRODUCTS'] as $product) {
                if (!in_array($product, $orderProducts)) {
                    $orderProducts[] = $product;
                }
            }
            if (!empty($_POST['MILEAGE'])) {
                CIBlockElement::SetPropertyValuesEx($carID, 2, ['MILEAGE' => $_POST['MILEAGE']]);
            }

            CIBlockElement::SetPropertyValuesEx($id, 3, [
                'STATUS' => $status,
                'MANAGER' => $manager,
                'DATE_END' => FormatDate("d.m.Y H:i:s"),
                'PRODUCTS' => $orderProducts,
                'MILEAGE' => $_POST['MILEAGE'],
                'COMMENT_MASTER' => '',
                'COMMENT_MANAGER' => $_POST['MASTER_COMMENT']
            ]);
            break;
    }
}
catch (Exception $ex) {
    die($ex->getMessage());
}

