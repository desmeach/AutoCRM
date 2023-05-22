<?php
/**
 * Created: 29.04.2023, 22:52
 * Author : Vladislav Naryzhny <desmeach@gmail.com>
 * Company: 34web Studio
 */

namespace lib\Controllers;

use lib\Models\CarsModel;
use lib\Models\ClientsModel;

class CarsController extends Controller {
    public static function getList(): array {
        $filter = self::getFilter();
        return CarsModel::getList($filter);
    }
    public static function getByID($request): ?array {
        return CarsModel::getItemByID($request);
    }
    public static function add() {
        if (!isset($_POST))
            return 'Ошибка запроса';
        return CarsModel::add($_POST);
    }
    public static function delete($ID): void {
        CarsModel::delete($ID);
    }
    public static function update() {
        header('Content-Type: json/application');
        return CarsModel::update();
    }
}