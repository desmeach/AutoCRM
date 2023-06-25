<?php
/**
 * Created: 08.03.2023, 19:25
 * Author : Vladislav Naryzhny <desmeach@gmail.com>
 * Company: 34web Studio
 */

namespace lib\Models;

use CIBlockElement;
use Exception;

class ProductsModel extends Model {
    private static int $IBLOCK_ID = 4;

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
                $branchesStr = self::formatArrayToNumericListStr($item['BRANCHES']['VALUE'], 'branches');
                $json[] = [
                    'id' => self::getItemDetailLink($item['ID'],
                        $item['ID'], 'products'),
                    'name' => $item['NAME'],
                    'branches' => $branchesStr,
                    'working_hour' => $item['WORKING_HOUR']['VALUE'],
                    'price' => $item['PRICE']['VALUE'],
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
        $props['BRANCHES']['VALUE'] = self::getLinkedItemsList(5, $props['BRANCHES']['VALUE']);
        return array_merge($fields, $props);
    }
    public static function add($data) {
        $ID = parent::addItem($data, self::$IBLOCK_ID);
        return $ID ?? ['error' => 'Ошибка при создании элемента'];
    }
    public static function update(): bool|string {
        return self::updateElem(self::$IBLOCK_ID);
    }
    public static function delete($ID): array {
        return self::deleteElem($ID);
    }
}