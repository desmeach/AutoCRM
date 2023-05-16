<?php
/**
 * Created: 05.04.2023, 20:29
 * Author : Vladislav Naryzhny <desmeach@gmail.com>
 * Company: 34web Studio
 */

require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_before.php');
CModule::IncludeModule('iblock');
use lib\IBlockSelector\ClientsSelector;
use lib\IBlockSelector\CarsSelector;

if ($_POST['IBLOCK_ID'] == 8)
    CUser::Delete($_POST['ID']);
else if ($_POST['IBLOCK_ID'] == 1) {
    $selector = new ClientsSelector();
    $client = $selector->getItemByID($_POST['ID']);
    $key = $selector->getUserKey();
    foreach ($client['KEY']['VALUE'] as $i => $clientKey)
        if ($key == $clientKey)
            unset($client['KEY']['VALUE'][$i]);
    $genderCodes = [
        'М' => 1,
        'Ж' => 2,
    ];
    $elem = new CIBlockElement();
    $selector = new CarsSelector();
    foreach ($client['CARS']['VALUE'] as $clientCar) {
        $car = $selector->getItemByID($clientCar['ID']);
        foreach ($car['KEY']['VALUE'] as $i => $carKey)
            if ($key == $carKey) {
                unset($car['KEY']['VALUE'][$i]);
            }
        $props = [
            'BRAND' => $car['BRAND']['VALUE'],
            'YEAR' => $car['YEAR']['VALUE'],
            'MILEAGE' => $car['MILEAGE']['VALUE'],
            'BODY' => $car['BODY']['VALUE'],
            'ENGINE' => $car['ENGINE']['VALUE'],
            'CHASSIS' => $car['CHASSIS']['VALUE'],
            'REG_NUM' => $car['REG_NUM']['VALUE'],
        ];
        if (count($car['KEY']['VALUE']) != 0)
            $props['KEY'] = $car['KEY']['VALUE'];
        $elem->Update($clientCar['ID'], ['PROPERTY_VALUES' => $props]);
    }
    $props = [
        'GENDER' => $genderCodes[$client['GENDER']['VALUE']],
        'CARS' => $client['CARS']['VALUE'],
        'PHONE' => $client['PHONE']['VALUE'],
    ];
    if (count($client['KEY']['VALUE']) != 0)
        $props['KEY'] = $client['KEY']['VALUE'];
    $elem->Update($_POST['ID'], ['PROPERTY_VALUES' => $props]);
}
else
    CIBlockElement::Delete($_POST['ID']);