<?php
/**
 * Created: 20.06.2023, 16:27
 * Author : Vladislav Naryzhny <desmeach@gmail.com>
 * Company: 34web Studio
 */

use autocrm_tables\lib\CarservicesPhonesTable;
use autocrm_tables\lib\CarservicesTable;
use autocrm_tables\lib\CarsTable;
use autocrm_tables\lib\ClientsTable;
use Bitrix\Main\Application;
use Bitrix\Main\Entity\Base;
use Bitrix\Main\Loader;
use Bitrix\Main\ModuleManager;

class autocrm_tables extends CModule {
    public function __construct() {
        $arModuleVersion = array();
        include __DIR__ . '/version.php';
        //присваиваем свойствам класса переменные из нашего файла
        if (is_array($arModuleVersion) && array_key_exists('VERSION', $arModuleVersion)) {
            $this->MODULE_VERSION = $arModuleVersion['VERSION'];
            $this->MODULE_VERSION_DATE = $arModuleVersion['VERSION_DATE'];
        }
        //пишем название нашего модуля как и директории
        $this->MODULE_ID = 'autocrm_tables';
        // название модуля
        $this->MODULE_NAME = 'Работа с сущностями таблиц';
        //описание модуля
        $this->MODULE_DESCRIPTION = 'Модуль для работы с сущностями в таблицах';
    }

    public function doInstall() {
        ModuleManager::registerModule($this->MODULE_ID);
        $this->installDB();
    }

    //вызываем метод удаления таблицы и удаляем модуль из регистра
    public function doUninstall() {
        $this->uninstallDB();
        ModuleManager::unRegisterModule($this->MODULE_ID);
    }

    //вызываем метод создания таблицы из выше подключенного класса
    public function installDB() {
        if (Loader::includeModule($this->MODULE_ID)) {
            ClientsTable::getEntity()->createDbTable();
            CarsTable::getEntity()->createDbTable();
            CarservicesPhonesTable::getEntity()->createDbTable();
            CarservicesTable::getEntity()->createDbTable();
        }
    }

    //вызываем метод удаления таблицы, если она существует
    public function uninstallDB() {
        if (Loader::includeModule($this->MODULE_ID)) {
            if (Application::getConnection()->isTableExists(Base::getInstance('\autocrm_tables\lib\ClientsTable')->getDBTableName())) {
                $connection = Application::getInstance()->getConnection();
                $connection->dropTable(ClientsTable::getTableName());
            }
            if (Application::getConnection()->isTableExists(Base::getInstance('\autocrm_tables\lib\CarservicesTable')->getDBTableName())) {
                $connection = Application::getInstance()->getConnection();
                $connection->dropTable(CarservicesTable::getTableName());
            }
            if (Application::getConnection()->isTableExists(Base::getInstance('\autocrm_tables\lib\CarsTable')->getDBTableName())) {
                $connection = Application::getInstance()->getConnection();
                $connection->dropTable(CarsTable::getTableName());
            }
            if (Application::getConnection()->isTableExists(Base::getInstance('\autocrm_tables\lib\CarservicesPhonesTable')->getDBTableName())) {
                $connection = Application::getInstance()->getConnection();
                $connection->dropTable(CarservicesPhonesTable::getTableName());
            }
        }
    }
}