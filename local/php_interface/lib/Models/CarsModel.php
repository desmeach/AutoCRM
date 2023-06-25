<?php
/**
 * Created: 08.03.2023, 19:25
 * Author : Vladislav Naryzhny <desmeach@gmail.com>
 * Company: 34web Studio
 */

namespace lib\Models;

use CIBlockElement;
use Exception;

class CarsModel extends Model {
    private static int $IBLOCK_ID = 2;

    public static function getList($filter): ?array {
        try {
            if (!$itemsList = parent::getListByIblockID(self::$IBLOCK_ID, $filter))
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
            return self::getProps(parent::getItemByIDFromIBlockID(self::$IBLOCK_ID, $ID));
        } catch (Exception $exception) {
            echo $exception->getMessage();
            return null;
        }
    }
    public static function getItemByVIN($vin): ?array {
        try {
            $car = CIBlockElement::GetList([], ['IBLOCK_ID' => self::$IBLOCK_ID, 'NAME' => $vin])->GetNextElement();
            if (!$car)
                return null;
            return self::getProps($car);
        } catch (Exception $exception) {
            echo $exception->getMessage();
            return null;
        }
    }
//    public static function getListForDataTable($filter): bool|string|null {
//        try {
//            $clients = self::getList($filter);
//            $json = [];
//            foreach ($clients as $client) {
//                $carsStr = self::formatArrayToNumericListStr($client['CARS']['VALUE'], 'cars');
//                $json[] = [
//                    'id' => self::getItemDetailLink($client['ID'],
//                        $client['ID'], 'clients'),
//                    'name' => $client['NAME'],
//                    'phone' => $client['PHONE']['VALUE'],
//                    'gender' => $client['GENDER']['VALUE'],
//                    'cars' => $carsStr,
//                    'email' => $client['EMAIL']['VALUE'],
//                ];
//            }
//            return json_encode($json);
//        } catch(Exception $exception) {
//            echo $exception->getMessage();
//            return null;
//        }
//    }
    private static function getProps($item): array {
        $props = $item->GetProperties();
        $fields = $item->GetFields();
        return array_merge($fields, $props);
    }
    public static function add($data) {
        $ID = parent::addItem($data, self::$IBLOCK_ID);
        return $ID ?? ['error' => 'Ошибка при создании элемента'];
    }
    public static function delete($ID): void {
        $car = CIBlockElement::GetList(false,
            ['IBLOCK_ID' => self::$IBLOCK_ID, 'ID' => $ID],
            false, false, ['PROPERTY_KEY']);
        $keys = [];
        $userKey = getKey();
        while ($props = $car->GetNext()) {
            $key = $props['PROPERTY_KEY_VALUE'];
            if ($key !== $userKey)
                $keys[] = $key;
        }
        if (empty($keys))
            $keys = '';
        CIBlockElement::SetPropertyValuesEx($ID, self::$IBLOCK_ID, ['KEY' => $keys]);
    }
    public static function update($props = []): bool|string {
        if (!empty($props))
            return self::updateElem(self::$IBLOCK_ID, $props);
        return self::updateElem(self::$IBLOCK_ID);
    }
}