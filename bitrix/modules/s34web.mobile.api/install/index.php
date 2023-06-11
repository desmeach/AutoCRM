<?php

use Bitrix\Main\Localization\Loc;

Loc::loadLanguageFile(__FILE__);

class s34web_mobile_api extends CModule
{
    var $MODULE_ID = 's34web.mobile.api';

    protected $eventManager;
    
    public function __construct()
    {
        $this->MODULE_ID = 's34web.mobile.api';
        $this->MODULE_NAME = '34web: MOBILE - REST API';
        $this->MODULE_DESCRIPTION = 'Модуль организовывает API-интерфейс мобильного приложения для сайта Mobile.tel';
        $this->MODULE_VERSION = '1.0.0';
        $this->MODULE_VERSION_DATE = '2020-09-30 15:00:00';
        $this->PARTNER_NAME = '34web.ru';
        $this->PARTNER_URI = 'https://34web.ru/';

        $this->eventManager = Bitrix\Main\EventManager::getInstance();
    }

    /**
     * Add catalog caching agent
     * The agent will start a minute after installation
     * @return $this
     */
    public function InstallAgents()
    {
        $time = new \Bitrix\Main\Type\DateTime();
        $time->add('+ 1 minutes');
        $runTime = $time->format('d.m.Y H:i:00');

        CAgent::AddAgent(
            '\s34web\Mobile\Api\controllers\v1\classes\Agent::cacheCatalog();',
            $this->MODULE_ID,
            'N',
            3600,
            '',
            'Y',
            $runTime,
            100
        );
        return $this;
    }

    /**
     * Remove catalog caching agent
     * @return $this
     */
    public function UnInstallAgents()
    {
        CAgent::RemoveAgent(
            '\s34web\Mobile\Api\controllers\v1\classes\Agent::cacheCatalog();',
            $this->MODULE_ID
        );
        return $this;
    }

    /**
     * Update iblock property 'EXTERNAL_TIMESTAMP' by events
     * type of 'EXTERNAL_TIMESTAMP' is DateTime
     */
    public function InstallEvents()
    {
        /** @see \s34web\Mobile\Api\controllers\v1\classes\Events::BitrixIblockSectionTableOnAfterUpdateHandler */
        $this->eventManager->registerEventHandler(
            'iblock',
            '\Bitrix\Iblock\SectionTable::onAfterUpdate',
            $this->MODULE_ID,
            \s34web\Mobile\Api\controllers\v1\classes\Events::class,
            'BitrixIblockSectionTableOnAfterUpdateHandler'
        );

        /** @see \s34web\Mobile\Api\controllers\v1\classes\Events::BitrixIblockSectionTableOnAfterDeleteHandler */
        $this->eventManager->registerEventHandler(
            'iblock',
            '\Bitrix\Iblock\SectionTable::onAfterDelete',
            $this->MODULE_ID,
            \s34web\Mobile\Api\controllers\v1\classes\Events::class,
            'BitrixIblockSectionTableOnAfterDeleteHandler'
        );

        /** @see \s34web\Mobile\Api\controllers\v1\classes\Events::onUpdateUserFieldValuesHandler */
        $this->eventManager->registerEventHandler(
            'main',
            'onUpdateUserFieldValues',
            $this->MODULE_ID,
            \s34web\Mobile\Api\controllers\v1\classes\Events::class,
            'onUpdateUserFieldValuesHandler'
        );

        /** @see \s34web\Mobile\Api\controllers\v1\classes\Events::BitrixCatalogPriceOnAfterUpdateHandler */
        $this->eventManager->registerEventHandler(
            'catalog',
            '\Bitrix\Catalog\Price::OnAfterUpdate',
            $this->MODULE_ID,
            \s34web\Mobile\Api\controllers\v1\classes\Events::class,
            'BitrixCatalogPriceOnAfterUpdateHandler'
        );

        /** @see \s34web\Mobile\Api\controllers\v1\classes\Events::BitrixCatalogProductOnBeforeUpdateHandler */
        $this->eventManager->registerEventHandler(
            'catalog',
            '\Bitrix\Catalog\Product::OnBeforeUpdate',
            $this->MODULE_ID,
            \s34web\Mobile\Api\controllers\v1\classes\Events::class,
            'BitrixCatalogProductOnBeforeUpdateHandler'
        );

        /** @see \s34web\Mobile\Api\controllers\v1\classes\Events::BitrixCatalogProductOnAfterUpdateHandler */
        $this->eventManager->registerEventHandler(
            'catalog',
            '\Bitrix\Catalog\Product::OnAfterUpdate',
            $this->MODULE_ID,
            \s34web\Mobile\Api\controllers\v1\classes\Events::class,
            'BitrixCatalogProductOnAfterUpdateHandler'
        );
    }

    /**
     *  Unregister all module events
     */
    public function UnInstallEvents()
    {
        $this->eventManager->unRegisterEventHandler(
            'iblock',
            '\Bitrix\Iblock\SectionTable::onAfterUpdate',
            $this->MODULE_ID,
            \s34web\Mobile\Api\controllers\v1\classes\Events::class,
            'BitrixIblockSectionTableOnAfterUpdateHandler'
        );

        $this->eventManager->unRegisterEventHandler(
            'iblock',
            '\Bitrix\Iblock\SectionTable::onAfterDelete',
            $this->MODULE_ID,
            \s34web\Mobile\Api\controllers\v1\classes\Events::class,
            'BitrixIblockSectionTableOnAfterDeleteHandler'
        );

        $this->eventManager->unRegisterEventHandler(
            'main',
            'onUpdateUserFieldValues',
            $this->MODULE_ID,
            \s34web\Mobile\Api\controllers\v1\classes\Events::class,
            'onUpdateUserFieldValuesHandler'
        );

        $this->eventManager->unRegisterEventHandler(
            'catalog',
            '\Bitrix\Catalog\Price::OnAfterUpdate',
            $this->MODULE_ID,
            \s34web\Mobile\Api\controllers\v1\classes\Events::class,
            'BitrixCatalogPriceOnAfterUpdateHandler'
        );

        /** @see \s34web\Mobile\Api\controllers\v1\classes\Events::BitrixCatalogProductOnBeforeUpdateHandler */
        $this->eventManager->unRegisterEventHandler(
          'catalog',
          '\Bitrix\Catalog\Product::OnBeforeUpdate',
          $this->MODULE_ID,
          \s34web\Mobile\Api\controllers\v1\classes\Events::class,
          'BitrixCatalogProductOnBeforeUpdateHandler'
        );

        $this->eventManager->unRegisterEventHandler(
            'catalog',
            '\Bitrix\Catalog\Product::OnAfterUpdate',
            $this->MODULE_ID,
            \s34web\Mobile\Api\controllers\v1\classes\Events::class,
            'BitrixCatalogProductOnAfterUpdateHandler'
        );
    }

    function DoInstall()
    {
        RegisterModule($this->MODULE_ID);

        $this->InstallEvents();
        $this->InstallAgents();

        $GLOBALS['APPLICATION']->IncludeAdminFile(Loc::getMessage('API_INSTALL_TITLE'), __DIR__ . '/step.php');
    }

    function DoUninstall()
    {
        UnRegisterModule($this->MODULE_ID);

        $this->UnInstallAgents();
        $this->UnInstallEvents();

        $GLOBALS['APPLICATION']->IncludeAdminFile(Loc::getMessage('API_UNINSTALL_TITLE'), __DIR__ . '/unstep.php');
    }
}