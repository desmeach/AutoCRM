<?php
/**
 * Created: 29.04.2023, 22:52
 * Author : Vladislav Naryzhny <desmeach@gmail.com>
 * Company: 34web Studio
 */

namespace lib\Controllers;

use lib\Models\BranchesModel;

class BranchesController extends Controller {
    public static function getList(): array {
        $filter = self::getFilter();
        return BranchesModel::getList($filter);
    }
    public static function getTableData(): bool|string {
        header('Content-Type: application/json');
        $filter = self::getFilter();
        return BranchesModel::getListForDataTable($filter);
    }
    public static function getByID($request): array {
        return BranchesModel::getItemByID($request);
    }
    public static function add($props = false) {
        if ($props)
            return BranchesModel::add($props);
        if (!isset($_POST))
            return 'Ошибка запроса';
        return BranchesModel::add($_POST);
    }
    public static function delete($ID): array {
        return BranchesModel::delete($ID);
    }
    public static function update() {
        header('Content-Type: json/application');
        return BranchesModel::update();
    }
}