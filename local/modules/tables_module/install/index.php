<?php

use Bitrix\Main\Application;
use Bitrix\Main\Config\Option;
use Bitrix\Main\Loader;
use TablesModule\Tables\ClientsTable;
use TablesModule\Tables\CarsTable;

/**
 * Created: 29.04.2023, 11:01
 * Author : Vladislav Naryzhny <desmeach@gmail.com>
 * Company: 34web Studio
 */

class tables_module extends CModule {
    var $MODULE_ID = "tables_module";
    var $MODULE_VERSION;
    var $MODULE_VERSION_DATE;
    var $MODULE_NAME;
    var $MODULE_DESCRIPTION;
    var $MODULE_CSS;

    function __construct() {
        $arModuleVersion = array();

        $path = str_replace("\\", "/", __FILE__);
        $path = substr($path, 0, strlen($path) - strlen("/index.php"));
        include($path . "/version.php");

        if (is_array($arModuleVersion) && array_key_exists("VERSION", $arModuleVersion)) {
            $this->MODULE_VERSION = $arModuleVersion["VERSION"];
            $this->MODULE_VERSION_DATE = $arModuleVersion["VERSION_DATE"];
        }

        $this->MODULE_NAME = "Модуль для работы с таблицами";
        $this->MODULE_DESCRIPTION = "После установки вы сможете пользоваться tables_module";
    }

    function InstallFiles() {
//        CopyDirFiles($_SERVER["DOCUMENT_ROOT"] . "/local/modules/dv_module/install/components",
//            $_SERVER["DOCUMENT_ROOT"] . "/bitrix/components", true, true);
        return true;
    }

    function UnInstallFiles() {
//        DeleteDirFilesEx("/local/components/dv");
        return true;
    }

    function InstallDB() {
        Loader::includeModule($this->MODULE_ID);

        if (!Application::getConnection(ClientsTable::getConnectionName())->isTableExists(
            Bitrix\Main\Entity\Base::getInstance('\TablesModule\Tables\ClientsTable')->getDBTableName()
            )
        ) {
            Bitrix\Main\Entity\Base::getInstance('\TablesModule\Tables\ClientsTable')->createDbTable();
        }

        if (!Application::getConnection(CarsTable::getConnectionName())->isTableExists(
            Bitrix\Main\Entity\Base::getInstance('\TablesModule\Tables\CarsTable')->getDBTableName()
        )
        ) {
            Bitrix\Main\Entity\Base::getInstance('\TablesModule\Tables\CarsTable')->createDbTable();
        }
    }

    function UnInstallDB() {
        Loader::includeModule($this->MODULE_ID);

        Application::getConnection(ClientsTable::getConnectionName())
            ->queryExecute('drop table if exists ' . Bitrix\Main\Entity\Base::getInstance('\TablesModule\Tables\ClientsTable')->getDBTableName());
        Application::getConnection(ClientsTable::getConnectionName())
            ->queryExecute('drop table if exists ' . Bitrix\Main\Entity\Base::getInstance('\TablesModule\Tables\CarsTable')->getDBTableName());
        Option::delete($this->MODULE_ID);
    }

    function DoInstall() {
        global $DOCUMENT_ROOT, $APPLICATION;
        $this->InstallFiles();
        $this->InstallDB();
        RegisterModule("tables_module");
        $APPLICATION->IncludeAdminFile("Установка модуля tables_module", $DOCUMENT_ROOT . "/local/modules/tables_module/install/step.php");
    }

    function DoUninstall() {
        global $DOCUMENT_ROOT, $APPLICATION;
        $this->UnInstallFiles();
        $this->UnInstallDB();
        UnRegisterModule("tables_module");
        $APPLICATION->IncludeAdminFile("Деинсталляция модуля tables_module", $DOCUMENT_ROOT . "/local/modules/tables_module/install/unstep.php");
    }
}
