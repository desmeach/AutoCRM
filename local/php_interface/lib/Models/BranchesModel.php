<?php
/**
 * Created: 01.05.2023, 17:43
 * Author : Vladislav Naryzhny <desmeach@gmail.com>
 * Company: 34web Studio
 */

namespace lib\Models;

use Exception;

class BranchesModel extends Model {
    private static int $IBLOCK_ID = 5;

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
    public static function getListForDataTable($filter): bool|string|null {
        try {
            $items = self::getList($filter);
            $json = [];
            foreach ($items as $item) {
                $coords = explode(';', $item['LOCATION']['VALUE']);
                $mapRef = '<a style="text-decoration: none; color: black;" href="https://yandex.ru/maps/?ll=' . $coords[1] . '%2C' . $coords[0] .
                    '&mode=search&sll=' . $coords[1] . '%2C' . $coords[0] . '&'
                    . 'text=' . $coords[0] . '%2C' . $coords[1] . '">'
                    . $item['LOCATION']['VALUE'] . '</a>';
                $phonesStr = "";
                foreach ($item['PHONES']['VALUE'] as $phone)
                    $phonesStr .= '<a style="text-decoration: none; color: black;" href="tel:' . $phone . '">' . $phone . '</a><br>';
                $json[] = [
                    'id' => self::getItemDetailLink($item['ID'],
                        $item['ID'], 'branches'),
                    'name' => $item['NAME'],
                    'address' => $item['ADDRESS']['VALUE'],
                    'location' => $mapRef,
                    'phones' => $phonesStr,
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
        return array_merge($fields, $props);
    }
    public static function add($data) {
        $ID = parent::addItem($data, self::$IBLOCK_ID);
        return $ID ?? ['error' => 'Ошибка при создании элемента'];
    }
    public static function delete($ID): array {
        return self::deleteElem($ID);
    }
    public static function update(): bool|string {
        return self::updateElem(self::$IBLOCK_ID);
    }
}