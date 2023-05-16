<?php
/**
 * Created: 04.04.2023, 2:56
 * Author : Vladislav Naryzhny <desmeach@gmail.com>
 * Company: 34web Studio
 */

namespace lib\Controllers;

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

class ControllerHandler {
    public static function handleAction() {
        $action = self::getAction();
        if (isset($action['error'])) {
            return $action['error'];
        }
        $controller = self::getController();
        switch ($action) {
            case 'getTableData':
                print_r($controller::getTableData());
                break;
            case 'get':
                break;
            case 'add':
                print_r($controller::add());
                break;
            case 'update':
                $controller::update();
                break;
            case 'getKanbanList':
                if ($controller != OrdersController::class)
                    return false;
                print_r($controller::getKanbanList());
                break;
            case 'getKanbanCard':
                if ($controller != OrdersController::class)
                    return false;
                print_r($controller::getKanbanCard($_POST['ID']));
                break;
//            case 'delete':
//                self::getMasters($json);
//                break;
//            case 'getByID':
//                self::getManagers($json);
//                break;
        }
    }
    private static function getEntity() {
        if (isset($_POST['ENTITY']))
            return $_POST['ENTITY'];
        return [
            'error' => 'Таблица с сущностью не найдена'
        ];
    }
    private static function getAction() {
        if (isset($_POST['ACTION']))
            return $_POST['ACTION'];
        return [
            'error' => 'Действие неопознано'
        ];
    }
    public static function getController($ENTITY = null) {
        if (!$ENTITY) {
            $ENTITY = self::getEntity();
            if (isset($ENTITY['error']))
                return $ENTITY['error'];
        }
        return match ($ENTITY) {
            'clients' => ClientsController::class,
            'cars' => CarsController::class,
            'orders' => OrdersController::class,
            'products' => ProductsController::class,
            'branches' => BranchesController::class,
            'masters' => MastersController::class,
            'managers' => ManagersController::class,
            default => null,
        };
    }
}

ControllerHandler::handleAction();
