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
    public static function update($props) {

    }
}