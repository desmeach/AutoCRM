<?php
/**
 * Created: 08.03.2023, 19:25
 * Author : Vladislav Naryzhny <desmeach@gmail.com>
 * Company: 34web Studio
 */

namespace lib\Models;

use CIBlockElement;
use Exception;
class ClientsModel extends Model {
    private static int $IBLOCK_ID = 1;

    public static function getList($filter): ?array {
        try {
            if (!$itemsList = self::getListByIblockID(self::$IBLOCK_ID, $filter))
                return null;
            $items  = [];
            while ($item = $itemsList->GetNextElement()) {
                $props = self::getProps($item);
                $items[$props['ID']] = $props;
            }
            return $items;
        } catch(Exception $exception) {
            echo $exception->getMessage();
            return null;
        }
    }
    public static function getItemByID($ID): ?array {
        try {
            return self::getProps(self::getItemByIDFromIBlockID(self::$IBLOCK_ID, $ID));
        } catch (Exception $exception) {
            echo $exception->getMessage();
            return null;
        }
    }
    public static function getItemByPhoneNumber($phone) {
        $arFilter = [
            'IBLOCK_ID' => 1,
            'PROPERTY_PHONE' => $phone
        ];
        $arSelect = [
            'ID', 'NAME', 'PROPERTY_EMAIL',
        ];
        $userData = CIBlockElement::GetList(false, $arFilter, false, false, $arSelect)->GetNextElement();
        return array_merge($userData->GetFields(), $userData->GetProperties());
    }
    public static function getListForDataTable($filter): bool|string|null {
        try {
            $items = self::getList($filter);
            $json = [];
            foreach ($items as $item) {
                $carsStr = self::formatArrayToNumericListStr($item['CARS']['VALUE'], 'cars');
                $json[] = [
                    'id' => self::getItemDetailLink($item['ID'],
                        $item['ID'], 'clients'),
                    'name' => $item['NAME'],
                    'phone' => $item['PHONE']['VALUE'],
                    'gender' => $item['GENDER']['VALUE'],
                    'cars' => $carsStr,
                    'email' => $item['EMAIL']['VALUE'],
                ];
            }
            return json_encode($json);
        } catch(Exception $exception) {
            echo $exception->getMessage();
            return null;
        }
    }
    private static function getProps($item): array {
        $props = $item->GetProperties();
        $fields = $item->GetFields();
        $props['CARS']['VALUE'] = self::getLinkedItemsList(2, $props['CARS']['VALUE']);
        return array_merge($fields, $props);
    }
    public static function add($data) {
        $ID = self::addItem($data, self::$IBLOCK_ID);
        return $ID ?? ['error' => 'Ошибка при создании элемента'];
    }
    public static function update(): bool|string {
        return self::updateElem(self::$IBLOCK_ID);
    }
    public static function delete($ID) {
        $client = CIBlockElement::GetList(false,
            ['IBLOCK_ID' => self::$IBLOCK_ID, 'ID' => $ID],
            false, false, ['PROPERTY_KEY', 'PROPERTY_CARS']);
        $keys = [];
        $cars = [];
        $userKey = getKey();
        while ($props = $client->GetNext()) {
            $key = $props['PROPERTY_KEY_VALUE'];
            $cars[$props['PROPERTY_CARS_VALUE']] = 1;
            if ($key !== $userKey)
                $keys[] = $key;
        }
        foreach ($cars as $carID => $value)
            CarsModel::delete($carID);
        if (empty($keys))
            $keys = '';
        CIBlockElement::SetPropertyValuesEx($ID, self::$IBLOCK_ID, ['KEY' => $keys]);
    }
}