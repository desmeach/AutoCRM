<?php
/**
 * Created: 29.04.2023, 22:52
 * Author : Vladislav Naryzhny <desmeach@gmail.com>
 * Company: 34web Studio
 */

namespace lib\Controllers;

use lib\Models\ProductsModel;

class ProductsController extends Controller {
    public static function getList(): array {
        $filter = self::getFilter();
        return ProductsModel::getList($filter);
    }
    public static function getTableData(): bool|string {
        header('Content-Type: application/json');
        $filter = self::getFilter();
        return ProductsModel::getListForDataTable($filter);
    }
    public static function getByID($request): array {
        return ProductsModel::getItemByID($request);
    }
    public static function add() {
        if (!isset($_POST))
            return 'Ошибка запроса';
        return ProductsModel::add($_POST);
    }
    public static function delete($ID): array {
        return ProductsModel::delete($ID);
    }
    public static function update() {
        header('Content-Type: json/application');
        return ProductsModel::update();
    }
}