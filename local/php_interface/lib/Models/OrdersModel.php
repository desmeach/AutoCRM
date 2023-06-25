<?php
/**
 * Created: 08.03.2023, 19:25
 * Author : Vladislav Naryzhny <desmeach@gmail.com>
 * Company: 34web Studio
 */

namespace lib\Models;

use CIBlockElement;
use Exception;

class OrdersModel extends Model {
    private static int $IBLOCK_ID = 3;

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
            $shortStatuses = [
                'Новая' => 'Новая',
                'Отклонена' => 'Откл.',
                'Запланирована' => 'Заплан.',
                'В работе' => 'В работе',
                'Рекламация' => 'Реклам.',
                'Завершена' => 'Заверш.',
            ];
            $orders = self::getList($filter);
            $json = [];
            foreach ($orders as $order) {
                $nameSplit = explode(' ', $order['CLIENT']['VALUE']['NAME']);
                $order['CLIENT']['VALUE']['NAME'] = $nameSplit[0];
                unset($nameSplit[0]);
                foreach ($nameSplit as $key => $initials) {
                    mb_internal_encoding("UTF-8");
                    $nameSplit[$key] = mb_strtoupper(mb_substr(trim($initials),0,1));
                }
                if ($nameSplit)
                    $order['CLIENT']['VALUE']['NAME'] = $order['CLIENT']['VALUE']['NAME'] . ' '
                        . implode('.', $nameSplit) . '.';
                $json[] = [
                    'id' => self::getItemDetailLink($order['ID'],
                        $order['ID'], 'orders'),
                    'date' => $order['DATE_CREATE'],
                    'client' => self::getItemDetailLink($order['CLIENT']['VALUE']['ID'],
                        $order['CLIENT']['VALUE']['NAME'], 'clients'),
                    'car' => self::getItemDetailLink($order['CAR']['VALUE']['ID'],
                        $order['CAR']['VALUE']['NAME'], 'cars'),
                    'status' => $shortStatuses[$order['STATUS']['VALUE']],
                    'products' => self::formatArrayToNumericListStr($order['PRODUCTS']['VALUE'], 'products'),
                    'total_price' => $order['TOTAL_PRICE']['VALUE'],
                    'date_receive' => $order['DATE_RECEIVE']['VALUE'],
                    'date_accept' => $order['DATE_ACCEPT']['VALUE'],
                    'date_start' => $order['DATE_START']['VALUE'],
                    'date_end' => $order['DATE_END']['VALUE'],
                ];
            }
            return json_encode($json);
        } catch(Exception $exception) {
            echo $exception->getMessage();
            return null;
        }
    }
    public static function getKanbanList($filter): bool|string|null {
        try {
            $filter[] = [[
                'LOGIC' => 'OR',
                ['PROPERTY_STATUS_VALUE' => 'Новая'],
                ['PROPERTY_STATUS_VALUE' => 'Запланирована'],
                ['PROPERTY_STATUS_VALUE' => 'В работе']
            ]];
            $orders = self::getList($filter);
            $json = [];
            foreach ($orders as $order) {
                $json[] = [
                    'id' => $order['ID'],
                    'client' => $order['CLIENT']['VALUE'],
                    'car' => $order['CAR']['VALUE'],
                    'products' => $order['PRODUCTS']['VALUE'],
                    'status' => $order['STATUS']['VALUE'],
                    'manager' => $order['MANAGER']['VALUE'],
                    'master' => $order['MASTER']['VALUE'],
                    'total_price' => $order['TOTAL_PRICE']['VALUE'],
                    'date_receive' => $order['DATE_RECEIVE']['VALUE'],
                    'date_accept' => $order['DATE_ACCEPT']['VALUE'],
                    'date_start' => $order['DATE_START']['VALUE'],
                    'date_end' => $order['DATE_END']['VALUE'],
                ];
            }
            return json_encode($json);
        } catch(Exception $exception) {
            echo $exception->getMessage();
            return null;
        }
    }
    public static function getKanbanCard($id): bool|string|null {
        try {
            $order = self::getItemByID($id);
            $json[] = [
                'id' => $order['ID'],
                'client' => $order['CLIENT']['VALUE'],
                'car' => $order['CAR']['VALUE'],
                'products' => $order['PRODUCTS']['VALUE'],
                'status' => $order['STATUS']['VALUE'],
                'manager' => $order['MANAGER']['VALUE'],
                'master' => $order['MASTER']['VALUE'],
                'total_price' => $order['TOTAL_PRICE']['VALUE'],
                'date_receive' => $order['DATE_RECEIVE']['VALUE'],
                'date_accept' => $order['DATE_ACCEPT']['VALUE'],
                'date_start' => $order['DATE_START']['VALUE'],
                'date_end' => $order['DATE_END']['VALUE'],
            ];
            return json_encode($json);
        } catch(Exception $exception) {
            echo $exception->getMessage();
            return null;
        }
    }
    private static function getProps($item): array {
        $props = $item->GetProperties();
        $fields = $item->GetFields();
        $props['CLIENT']['VALUE'] = ClientsModel::getItemByID($props['CLIENT']['VALUE']);
        $props['CAR']['VALUE'] = CarsModel::getItemByID($props['CAR']['VALUE']);
        $props['MANAGER']['VALUE'] = ManagersModel::getItemByID($props['MANAGER']['VALUE']);
        $props['MASTER']['VALUE'] = MastersModel::getItemByID($props['MASTER']['VALUE']);
        $props['PRODUCTS']['VALUE'] = self::getLinkedItemsList(4, $props['PRODUCTS']['VALUE']);
        $props['BRANCH']['VALUE'] = BranchesModel::getItemByID($props['BRANCH']['VALUE']);
        return array_merge($fields, $props);
    }
    public static function add($data) {
        global $USER;
        $NAME = 'Заказ клиента ID' . $data['CLIENT'];
        unset($_POST['IBLOCK_ID'], $data['NAME']);
        $PROPS = parent::formatFormRequest($data);
        if (isset($data['DATE_RECEIVE']))
            $PROPS['DATE_RECEIVE'] = $data['DATE_RECEIVE'];
        else
            $PROPS['DATE_RECEIVE'] = date('d.m.Y H:i:s');
        $PROPS['STATUS'] = 3;
        $totalSum = 0;
        if (!is_array($data['PRODUCTS']))
            $data['PRODUCTS'] = [$data['PRODUCTS']];
        foreach ($data['PRODUCTS'] as $id) {
            $product = ProductsModel::getItemByID($id);
            $totalSum += $product['PRICE']['VALUE'];
        }
        $PROPS['TOTAL_PRICE'] = $totalSum;
        $el = new CIBlockElement;
        $arFields = [
            'MODIFIED_BY' => $USER->GetID(),
            'NAME' => $NAME,
            'IBLOCK_ID' => self::$IBLOCK_ID,
            'PROPERTY_VALUES' => $PROPS
        ];
        $ID = $el->Add($arFields);
        return $ID ?? ['error' => 'Ошибка при создании элемента'];
    }
    public static function update(): bool|string {
        return self::updateElem(self::$IBLOCK_ID);
    }
    public static function delete($ID): array {
        return self::deleteElem($ID);
    }
}