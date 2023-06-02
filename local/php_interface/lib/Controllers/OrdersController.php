<?php
/**
 * Created: 29.04.2023, 22:52
 * Author : Vladislav Naryzhny <desmeach@gmail.com>
 * Company: 34web Studio
 */

namespace lib\Controllers;

use lib\Models\OrdersModel;

class OrdersController extends Controller {
    public static function getList(): array {
        $filter = self::getFilter();
        return OrdersModel::getList($filter);
    }
    public static function getKanbanList(): bool|string {
        header('Content-Type: application/json');
        $filter = self::getFilter();
        return OrdersModel::getKanbanList($filter);
    }
    public static function getTableData(): bool|string {
        header('Content-Type: application/json');
        $filter = self::getFilter();
        return OrdersModel::getListForDataTable($filter);
    }
    public static function getByID($request): array {
        return OrdersModel::getItemByID($request);
    }
    public static function getKanbanCard($id): bool|string|null {
        header('Content-Type: application/json');
        return OrdersModel::getKanbanCard($id);
    }
    public static function add() {
        if (!isset($_POST))
            return 'Ошибка запроса';
        return OrdersModel::add($_POST);
    }
    public static function delete($ID): array {
        return OrdersModel::delete($ID);
    }
    public static function update() {
        header('Content-Type: json/application');
        return OrdersModel::update();
    }
}