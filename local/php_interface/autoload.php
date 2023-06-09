<?php
/**
 * Created: 08.03.2023, 18:49
 * Author : Vladislav Naryzhny <desmeach@gmail.com>
 * Company: 34web Studio
 */

Bitrix\Main\Loader::registerAutoLoadClasses(null, [
    'lib\Statistics\ClientsStatistic' => '/local/php_interface/lib/Statistics/ClientsStatistic.php',
    'lib\Statistics\ProductsStatistic' => '/local/php_interface/lib/Statistics/ProductsStatistic.php',
    'lib\Statistics\OrdersStatistic' => '/local/php_interface/lib/Statistics/OrdersStatistic.php',
    'lib\ReportsGenerator' => '/local/php_interface/lib/ReportsGenerator.php',

    'lib\Models\Model' => '/local/php_interface/lib/Models/Model.php',
    'lib\Models\ClientsModel' => '/local/php_interface/lib/Models/ClientsModel.php',
    'lib\Models\CarsModel' => '/local/php_interface/lib/Models/CarsModel.php',
    'lib\Models\OrdersModel' => '/local/php_interface/lib/Models/OrdersModel.php',
    'lib\Models\MastersModel' => '/local/php_interface/lib/Models/MastersModel.php',
    'lib\Models\ProductsModel' => '/local/php_interface/lib/Models/ProductsModel.php',
    'lib\Models\BranchesModel' => '/local/php_interface/lib/Models/BranchesModel.php',
    'lib\Models\ManagersModel' => '/local/php_interface/lib/Models/ManagersModel.php',

    'lib\Controllers\Controller' => '/local/php_interface/lib/Controllers/Controller.php',
    'lib\Controllers\ControllerHandler' => '/local/php_interface/lib/Controllers/ControllerHandler.php',
    'lib\Controllers\ClientsController' => '/local/php_interface/lib/Controllers/ClientsController.php',
    'lib\Controllers\CarsController' => '/local/php_interface/lib/Controllers/CarsController.php',
    'lib\Controllers\OrdersController' => '/local/php_interface/lib/Controllers/OrdersController.php',
    'lib\Controllers\MastersController' => '/local/php_interface/lib/Controllers/MastersController.php',
    'lib\Controllers\ProductsController' => '/local/php_interface/lib/Controllers/ProductsController.php',
    'lib\Controllers\BranchesController' => '/local/php_interface/lib/Controllers/BranchesController.php',
    'lib\Controllers\ManagersController' => '/local/php_interface/lib/Controllers/ManagersController.php',

    'autocrm_tables\lib\ClientsTable' => '/local/modules/autocrm_tables/lib/ClientsTable.php',
    'autocrm_tables\lib\CarsTable' => '/local/modules/autocrm_tables/lib/CarsTable.php',
    'autocrm_tables\lib\CarservicesTable' => '/local/modules/autocrm_tables/lib/CarservicesTable.php',
    'autocrm_tables\lib\CarservicesPhonesTable' => '/local/modules/autocrm_tables/lib/CarservicesPhonesTable.php',

    'autocrm_tables\lib\controllers\Controller' => '/local/modules/autocrm_tables/lib/controllers/Controller.php',
    'autocrm_tables\lib\controllers\ClientsController' => '/local/modules/autocrm_tables/lib/controllers/ClientsController.php',
    'autocrm_tables\lib\controllers\CarservicesController' => '/local/modules/autocrm_tables/lib/controllers/CarservicesController.php',

    'autocrm_tables\lib\controllers\ControllersHandler' => '/local/modules/autocrm_tables/lib/controllers/ControllersHandler.php',
]);
