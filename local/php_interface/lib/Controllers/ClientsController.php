<?php
/**
 * Created: 29.04.2023, 22:52
 * Author : Vladislav Naryzhny <desmeach@gmail.com>
 * Company: 34web Studio
 */

namespace lib\Controllers;

use lib\Models\ClientsModel;

class ClientsController extends Controller {
    public static function getList(): array {
        $filter = self::getFilter();
        return ClientsModel::getList($filter);
    }
    public static function getTableData(): bool|string {
        header('Content-Type: application/json');
        $filter = self::getFilter();
        return ClientsModel::getListForDataTable($filter);
    }
    public static function getByID($request): ?array {
        return ClientsModel::getItemByID($request);
    }
    public static function add() {
        if (!isset($_POST))
            return 'Ошибка запроса';
        return ClientsModel::add($_POST);
    }
}