<?php
/**
 * Created: 29.04.2023, 22:52
 * Author : Vladislav Naryzhny <desmeach@gmail.com>
 * Company: 34web Studio
 */

namespace lib\Controllers;

use lib\Models\MastersModel;

class MastersController extends Controller {
    public static function getList(): array {
        $filter = self::getFilter();
        return MastersModel::getList($filter);
    }
    public static function getTableData(): bool|string {
        header('Content-Type: application/json');
        $filter = self::getFilter();
        return MastersModel::getListForDataTable($filter);
    }
    public static function getByID($request): array {
        return MastersModel::getItemByID($request);
    }
    public static function add() {
        if (!isset($_POST))
            return 'Ошибка запроса';
        return MastersModel::add($_POST);
    }
}