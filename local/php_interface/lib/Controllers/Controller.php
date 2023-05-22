<?php
/**
 * Created: 29.04.2023, 22:54
 * Author : Vladislav Naryzhny <desmeach@gmail.com>
 * Company: 34web Studio
 */

namespace lib\Controllers;

use lib\Models\CarsModel;

abstract class Controller {
    protected static function getFilter(): array {
        $arFilter = [];
        if (isset($_POST['date-from']))
            $arFilter['>=DATE_CREATE'] = $_POST['date-from'] . " 00:00:00";
        if (isset($_POST['date-to']))
            $arFilter['<=DATE_CREATE'] = $_POST['date-to'] . " 23:59:59";
        if (isset($_POST['status']) && $_POST['status'] != 'Все') {
            $arFilter['PROPERTY_STATUS_VALUE'] = $_POST['status'];
        }
        if (isset($_POST['branch']) && $_POST['branch'] != 'Все') {
            $arFilter['PROPERTY_BRANCH'] = $_POST['branch'];
            $arFilter['PROPERTY_BRANCHES'] = $_POST['branch'];
        }
        if (isset($_POST['vin'])) {
            $arFilter['PROPERTY_CAR.NAME'] = '%' . $_POST['vin'] . '%';
        }
        if (isset($_POST['client'])) {
            $arFilter['PROPERTY_CLIENT.NAME'] = '%' . $_POST['client'] . '%';
        }
        if (isset($_POST['products'])) {
            $arFilter['PROPERTY_PRODUCTS.ID'] = $_POST['products'];
        }
        if (isset($_POST['master']) && $_POST['master'] != 'Все') {
            $arFilter['PROPERTY_MASTER'] = $_POST['master'];
        }
        if (isset($_POST['manager']) && $_POST['manager'] != 'Все') {
            $arFilter['PROPERTY_MANAGER'] = $_POST['manager'];
        }
        return $arFilter;
    }
}