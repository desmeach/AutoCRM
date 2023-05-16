<?php
/**
 * Created: 29.04.2023, 22:54
 * Author : Vladislav Naryzhny <desmeach@gmail.com>
 * Company: 34web Studio
 */

namespace lib\Controllers;

abstract class Controller {
    protected static function getFilter(): array {
        $arFilter = [];
        if (isset($_POST['date-from']))
            $arFilter['>=DATE_CREATE'] = $_POST['date-from'] . " 00:00:00";
        if (isset($_POST['date-to']))
            $arFilter['<=DATE_CREATE'] = $_POST['date-to'] . " 23:59:59";
        if (isset($_POST['status']) && $_POST['status'] != 'Все')
            $arFilter['PROPERTY_STATUS_VALUE'] = $_POST['status'];
        if (isset($_POST['branch']) && $_POST['branch'] != 'Все')
            $arFilter['PROPERTY_BRANCH_VALUE'] = $_POST['branch'];
        return $arFilter;
    }
}