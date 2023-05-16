<?php
/**
 * Created: 01.05.2023, 17:39
 * Author : Vladislav Naryzhny <desmeach@gmail.com>
 * Company: 34web Studio
 */

namespace lib\Models;

use Exception;

class MastersModel extends Model {
    private static int $IBLOCK_ID = 7;

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
                $json[] = [
                    'id' => self::getItemDetailLink($item['ID'],
                        $item['ID'], 'masters'),
                    'name' => $item['NAME'],
                    'branch' => self::getItemDetailLink($item['BRANCH']['VALUE']['ID'],
                        $item['BRANCH']['VALUE']['NAME'], 'branches'),
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
        $props['BRANCH']['VALUE'] = BranchesModel::getItemByID($props['BRANCH']['VALUE']);
        return array_merge($fields, $props);
    }
    public static function add($data) {
        $ID = parent::addItem($data, self::$IBLOCK_ID);
        return $ID ?? ['error' => 'Ошибка при создании элемента'];
    }
}