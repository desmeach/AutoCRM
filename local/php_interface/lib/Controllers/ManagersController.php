<?php
/**
 * Created: 29.04.2023, 22:52
 * Author : Vladislav Naryzhny <desmeach@gmail.com>
 * Company: 34web Studio
 */

namespace lib\Controllers;

use lib\Models\ManagersModel;

class ManagersController extends Controller {
    public static function getList(): array {
        $filter = self::getFilter();
        return ManagersModel::getList($filter);
    }
    public static function getTableData(): bool|string {
        header('Content-Type: application/json');
        $filter = self::getFilter();
        return ManagersModel::getListForDataTable($filter);
    }
    public static function getByID($request): array {
        return ManagersModel::getItemByID($request);
    }
    public static function add() {
        if (!isset($_POST))
            return 'Ошибка запроса';
        return ManagersModel::add($_POST);
    }
}