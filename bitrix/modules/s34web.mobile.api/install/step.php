<?php

use Bitrix\Main\Localization\Loc;

//Создание HL MobileUserAuthData
use Bitrix\Main\Loader;
Loader::IncludeModule('highloadblock');
use Bitrix\Highloadblock as HL;
use Bitrix\Main\Mail\Internal\EventMessageSiteTable;
use Bitrix\Main\Mail\Internal\EventMessageTable;
use Bitrix\Main\Mail\Internal\EventTypeTable;
use Bitrix\Main\SiteTable;
use Bitrix\Main\Sms\TemplateTable;
use Bitrix\Main\UserTable;

$site_id = "s1";

$result = \Bitrix\Highloadblock\HighloadBlockTable::getList(array('filter'=>array('=TABLE_NAME'=>"b_hlbd_mobile_user_auth_data")));

if(!$result->fetch()) {
  $result = HL\HighloadBlockTable::add(array(
    'NAME' => 'MobileUserAuthData',
    'TABLE_NAME' => 'b_hlbd_mobile_user_auth_data',
  ));

  if ($result->isSuccess()) {
    $id = $result->getId();
    $arLangs = [
      "ru" => "Подтверждение нескольких устройств для авторизации",
      "en" => "Multi auth user",
    ];
    $ok = true;
    foreach ($arLangs as $lang_key => $lang_val) {
      $result = HL\HighloadBlockLangTable::add(array(
        'ID' => $id,
        'LID' => $lang_key,
        'NAME' => $lang_val
      ));
      $ok = $ok && $result->isSuccess();
    }

    if ($ok) {
      //Добавление полей в HL
      $UFObject = 'HLBLOCK_' . $id;
      $arCartFields = array(
        'UF_PHONE' => array(
          'ENTITY_ID' => $UFObject,
          'FIELD_NAME' => 'UF_PHONE',
          'USER_TYPE_ID' => 'string',
          'MANDATORY' => 'Y',
          "EDIT_FORM_LABEL" => array('ru' => 'Телефон', 'en' => 'Phone'),
          "LIST_COLUMN_LABEL" => array('ru' => 'Телефон', 'en' => 'Phone'),
          "LIST_FILTER_LABEL" => array('ru' => 'Телефон', 'en' => 'Phone'),
          "ERROR_MESSAGE" => array('ru' => '', 'en' => ''),
          "HELP_MESSAGE" => array('ru' => '', 'en' => ''),
        ),
        'UF_DEVICE_TOKEN' => array(
          'ENTITY_ID' => $UFObject,
          'FIELD_NAME' => 'UF_DEVICE_TOKEN',
          'USER_TYPE_ID' => 'string',
          'MANDATORY' => 'Y',
          "EDIT_FORM_LABEL" => array('ru' => 'Ид устройства', 'en' => 'Device token'),
          "LIST_COLUMN_LABEL" => array('ru' => 'Ид устройства', 'en' => 'Device token'),
          "LIST_FILTER_LABEL" => array('ru' => 'Ид устройства', 'en' => 'Device token'),
          "ERROR_MESSAGE" => array('ru' => '', 'en' => ''),
          "HELP_MESSAGE" => array('ru' => '', 'en' => ''),
        ),
        'UF_TIME_VALID' => array(
          'ENTITY_ID' => $UFObject,
          'FIELD_NAME' => 'UF_TIME_VALID',
          'USER_TYPE_ID' => 'datetime',
          'MANDATORY' => 'Y',
          "EDIT_FORM_LABEL" => array('ru' => 'Дата добавления', 'en' => 'Date added'),
          "LIST_COLUMN_LABEL" => array('ru' => 'Дата добавления', 'en' => 'Date added'),
          "LIST_FILTER_LABEL" => array('ru' => 'Дата добавления', 'en' => 'Date added'),
          "ERROR_MESSAGE" => array('ru' => '', 'en' => ''),
          "HELP_MESSAGE" => array('ru' => '', 'en' => ''),
        ),
        'UF_CODE' => array(
          'ENTITY_ID' => $UFObject,
          'FIELD_NAME' => 'UF_CODE',
          'USER_TYPE_ID' => 'string',
          'MANDATORY' => 'Y',
          "EDIT_FORM_LABEL" => array('ru' => 'Код подтверждения', 'en' => 'Sms code'),
          "LIST_COLUMN_LABEL" => array('ru' => 'Код подтверждения', 'en' => 'Sms code'),
          "LIST_FILTER_LABEL" => array('ru' => 'Код подтверждения', 'en' => 'Sms code'),
          "ERROR_MESSAGE" => array('ru' => '', 'en' => ''),
          "HELP_MESSAGE" => array('ru' => '', 'en' => ''),
        ),
        'UF_USER_ID' => array(
          'ENTITY_ID' => $UFObject,
          'FIELD_NAME' => 'UF_USER_ID',
          'USER_TYPE_ID' => 'integer',
          'MANDATORY' => 'Y',
          "EDIT_FORM_LABEL" => array('ru' => 'ИД пользователя для связи', 'en' => 'User ID'),
          "LIST_COLUMN_LABEL" => array('ru' => 'ИД пользователя для связи', 'en' => 'User ID'),
          "LIST_FILTER_LABEL" => array('ru' => 'ИД пользователя для связи', 'en' => 'User ID'),
          "ERROR_MESSAGE" => array('ru' => '', 'en' => ''),
          "HELP_MESSAGE" => array('ru' => '', 'en' => ''),
        ),
        'UF_LAST_TIME_SEND' => array(
          'ENTITY_ID' => $UFObject,
          'FIELD_NAME' => 'UF_LAST_TIME_SEND',
          'USER_TYPE_ID' => 'datetime',
          'MANDATORY' => 'Y',
          "EDIT_FORM_LABEL" => array('ru' => 'Время последней отправки', 'en' => 'Last sended time'),
          "LIST_COLUMN_LABEL" => array('ru' => 'Время последней отправки', 'en' => 'Last sended time'),
          "LIST_FILTER_LABEL" => array('ru' => 'Время последней отправки', 'en' => 'Last sended time'),
          "ERROR_MESSAGE" => array('ru' => '', 'en' => ''),
          "HELP_MESSAGE" => array('ru' => '', 'en' => ''),
        ),
        'UF_TOKEN' => array(
          'ENTITY_ID' => $UFObject,
          'FIELD_NAME' => 'UF_TOKEN',
          'USER_TYPE_ID' => 'string',
          'MANDATORY' => 'Y',
          "EDIT_FORM_LABEL" => array('ru' => 'Токен для авторизации', 'en' => 'Token'),
          "LIST_COLUMN_LABEL" => array('ru' => '', 'en' => ''),
          "LIST_FILTER_LABEL" => array('ru' => '', 'en' => ''),
          "ERROR_MESSAGE" => array('ru' => '', 'en' => ''),
          "HELP_MESSAGE" => array('ru' => '', 'en' => ''),
        ),
        'UF_REFRESH_TOKEN' => array(
          'ENTITY_ID' => $UFObject,
          'FIELD_NAME' => 'UF_REFRESH_TOKEN',
          'USER_TYPE_ID' => 'string',
          'MANDATORY' => 'Y',
          "EDIT_FORM_LABEL" => array('ru' => 'Токен для продления токена авторизации', 'en' => 'Refresh token'),
          "LIST_COLUMN_LABEL" => array('ru' => '', 'en' => ''),
          "LIST_FILTER_LABEL" => array('ru' => '', 'en' => ''),
          "ERROR_MESSAGE" => array('ru' => '', 'en' => ''),
          "HELP_MESSAGE" => array('ru' => '', 'en' => ''),
        ),
        'UF_REFRESH_TIME' => array(
          'ENTITY_ID' => $UFObject,
          'FIELD_NAME' => 'UF_REFRESH_TIME',
          'USER_TYPE_ID' => 'datetime',
          'MANDATORY' => 'Y',
          "EDIT_FORM_LABEL" => array('ru' => 'Время поcледнего обновления токена', 'en' => 'Last token update time'),
          "LIST_COLUMN_LABEL" => array('ru' => '', 'en' => ''),
          "LIST_FILTER_LABEL" => array('ru' => '', 'en' => ''),
          "ERROR_MESSAGE" => array('ru' => '', 'en' => ''),
          "HELP_MESSAGE" => array('ru' => '', 'en' => ''),
        ),
      );
      $arSavedFieldsRes = array();
      foreach ($arCartFields as $arCartField) {
        $obUserField = new CUserTypeEntity;
        $ID = $obUserField->Add($arCartField);
        $arSavedFieldsRes[] = $ID;
      }
    }

    /**
     * Добавление пользовательского свойства
     */
    $oUserTypeEntity = new CUserTypeEntity();

    $aUserFields = array(
      /*
      *  Идентификатор сущности, к которой будет привязано свойство.
      * Для секция формат следующий - IBLOCK_{IBLOCK_ID}_SECTION
      */
      'ENTITY_ID' => 'USER',
      /* Код поля. Всегда должно начинаться с UF_ */
      'FIELD_NAME' => 'UF_LOCATION_ID',
      /* Указываем, что тип нового пользовательского свойства строка */
      'USER_TYPE_ID' => 'string',
      /*
      * XML_ID пользовательского свойства.
      * Используется при выгрузке в качестве названия поля
      */
      'XML_ID' => 'UF_LOCATION_ID_FIELD',
      /* Сортировка */
      'SORT' => 100,
      /* Является поле множественным или нет */
      'MULTIPLE' => 'N',
      /* Обязательное или нет свойство */
      'MANDATORY' => 'N',
      /*
      * Показывать в фильтре списка. Возможные значения:
      * не показывать = N, точное совпадение = I,
      * поиск по маске = E, поиск по подстроке = S
      */
      'SHOW_FILTER' => 'N',
      /*
      * Не показывать в списке. Если передать какое-либо значение,
      * то будет считаться, что флаг выставлен.
      */
      'SHOW_IN_LIST' => '',
      /*
      * Пустая строка разрешает редактирование.
      * Если передать какое-либо значение, то будет считаться,
      * что флаг выставлен.

      */
      'EDIT_IN_LIST' => '',
      /* Значения поля участвуют в поиске */
      'IS_SEARCHABLE' => 'N',
      /*
      * Дополнительные настройки поля (зависят от типа).
      * В нашем случае для типа string
      */
      'SETTINGS' => array(
        /* Значение по умолчанию */
        'DEFAULT_VALUE' => '',
        /* Размер поля ввода для отображения */
        'SIZE' => '20',
        /* Количество строчек поля ввода */
        'ROWS' => '1',
        /* Минимальная длина строки (0 - не проверять) */
        'MIN_LENGTH' => '0',
        /* Максимальная длина строки (0 - не проверять) */
        'MAX_LENGTH' => '0',
        /* Регулярное выражение для проверки */
        'REGEXP' => '',
      ),
      /* Подпись в форме редактирования */
      'EDIT_FORM_LABEL' => array(
        'ru' => 'ID местоположения',
        'en' => 'Location ID',
      ),
      /* Заголовок в списке */
      'LIST_COLUMN_LABEL' => array(
        'ru' => 'ID местоположения',
        'en' => 'Location ID',
      ),
      /* Подпись фильтра в списке */
      'LIST_FILTER_LABEL' => array(
        'ru' => 'ID местоположения',
        'en' => 'Location ID',
      ),
      /* Сообщение об ошибке (не обязательное) */
      'ERROR_MESSAGE' => array(
        'ru' => '',
        'en' => '',
      ),
      /* Помощь */
      'HELP_MESSAGE' => array(
        'ru' => '',
        'en' => '',
      ),
    );

    $iUserFieldId = $oUserTypeEntity->Add($aUserFields); // int

    $users = \Bitrix\Main\UserTable::getList(array("filter"=>["EMAIL"=>"fake@stimul.tel"], "select" => array("ID")));
    if (!$users->fetch())
    {
      //Добавление фейкового пользователя
      $user = new CUser;
      $arFields = array(
        "NAME" => "Временный",
        "LOGIN" => "fake_user_for_mobile",
        "EMAIL" => "fake@stimul.tel",
        "PHONE_NUMBER" => "+79003456789",
        "LID" => "ru",
        "ACTIVE" => "Y",
        "PASSWORD" => "VDE125753dfw",
        "CONFIRM_PASSWORD" => "VDE125753dfw",
        //      "GROUP_ID" => array(10, 11)
      );
      $new_user_ID = $user->Add($arFields);
    }

    //Настройка навигации

    CUrlRewriter::Add(
      array(
        "SITE_ID" => $site_id,
        "CONDITION" => "#^/api/#",
        "ID" => "s34web.mobile.api",
        "PATH" => "/api/index.php",
        "RULE" => ""
      )
    );

    //Установка шаблона смс
    $event_type_is_create = false;
    $list = EventTypeTable::getList([
      'filter'=>['EVENT_NAME'=>'SMS_MOBILE_USER_CONFIRM_NUMBER']
    ]);
    if (!$list->fetch())
    {
      $eventTypeAdd = EventTypeTable::add([
        'EVENT_NAME'    => 'SMS_MOBILE_USER_CONFIRM_NUMBER',
        'EVENT_TYPE'    => 'sms',
        'NAME'          => 'Подтверждение пользователя мобильного приложения по смс',
        'LID'           => 'ru',
        'DESCRIPTION'   => "#USER_PHONE# - номер телефона
#CODE# - код подтверждения"
      ]);
      if($eventTypeAdd->isSuccess()){
        $event_type_is_create = true;
      }else{
        echo "Ошибка создания шаблона почтового события: ".implode(',',$eventTypeAdd->getErrorMessages()).PHP_EOL;
      }
    }else{
      $event_type_is_create = true;
      echo "Тип смс уведомления уже создан".PHP_EOL;
    }

    if($event_type_is_create)
    {
      $list = TemplateTable::getList([
        'select' => ['ID'],
        'filter' => [
          'EVENT_NAME'=> 'SMS_MOBILE_USER_CONFIRM_NUMBER',
        ],
      ]);

      if(!$list->fetch())
      {
        //Не добавляет обязательное поле site_id
        $eventMessageAdd = TemplateTable::add([
          'ACTIVE'      => 'Y',
          'LANGUAGE_ID' => 'ru',
          'EVENT_NAME'  => 'SMS_MOBILE_USER_CONFIRM_NUMBER',
          'SENDER'      => "#DEFAULT_SENDER#",
          'RECEIVER'    => "#USER_PHONE#",
          'SITE_ID'     => [$site_id],
          'MESSAGE'     => "Код для подтверждения: #CODE#"
        ]);

        if ($eventMessageAdd->isSuccess())
        {
          echo "Успешно создано смс событие" . PHP_EOL;
        } else {
          echo "Ошибка создания шаблона почтового события: " . implode(',', $eventMessageAdd->getErrorMessages()) . PHP_EOL;
        }
      }
    }

    //Добавить группу
    /*
     * NAME = Мобильные пользователи
     * CODE = mobile_users

      Для инфоблока Услуги
      Скрывать в мобильном приложении ( HIDE_IN_MOBILE   тип список   YES=Y, Скрывать услуги при выгрузке через апи в мобильное приложение)
      Детальная карточка для мобильного приложения(DETAIL_MOBILE тип HTML/text, Описание для мобильного приложения, заменяет детальное описание при выгрузке в АПИ)

      Для инфоблока Акции
      Скрывать в мобильном приложении ( HIDE_IN_MOBILE   тип список   YES=Y, Скрывать акцию при выгрузке через апи в мобильное приложение)
      Детальная карточка для мобильного приложения(DETAIL_MOBILE тип HTML/text, Описание для мобильного приложения, заменяет детальное описание при выгрузке в АПИ)


      Проверить, чтобы было создано поле адрес и у него стояла галочка является адресом.


    */

    //Установка базовых параметров
    \Bitrix\Main\Config\Option::set("s34web.mobile.api","PATH_RESTFUL_API","/api/");
    \Bitrix\Main\Config\Option::set("s34web.mobile.api","OPERATING_MODE","OBJECT_ORIENTED");
    \Bitrix\Main\Config\Option::set("s34web.mobile.api","SUPPORT_LOG_PATH","/logs/api.log");
    \Bitrix\Main\Config\Option::set("s34web.mobile.api","USE_VERSIONS","Y");
    \Bitrix\Main\Config\Option::set("s34web.mobile.api","USE_RESTFUL_API","N");
    \Bitrix\Main\Config\Option::set("s34web.mobile.api","IS_SEND_SMS","Y");
    /*\Bitrix\Main\Config\Option::set("s34web.mobile.api","PAYMENTS_SBER_TOKEN","243bcm6ge7ag3qibmlt0ophuni"); тест*/
    \Bitrix\Main\Config\Option::set("s34web.mobile.api","PAYMENTS_SBER_TOKEN","gt4si9dhgb232ilpgd31o3i97q");
    \Bitrix\Main\Config\Option::set('s34web.mobile.api', 'catalog_cache_version', 'new');
    \Bitrix\Main\Config\Option::set('s34web.mobile.api', 'DADATA_TOKEN', 'f80b7fede64be0c67509ee8e07a089e28ac1c5b5');



    $list = SiteTable::getList([
      "filter" => [
        'ACTIVE' => 'Y',
        'DEF' => 'Y',
      ]
    ]);
    if ($row = $list->fetch())
    {
      $API_SERVER_NAME = $row['SERVER_NAME'];
    } else {
      $API_SERVER_NAME = \Bitrix\Main\Config\Option::get("main","SERVER_NAME","stimul.tel");
    }
    \Bitrix\Main\Config\Option::set("s34web.mobile.api","API_SERVER_NAME", $API_SERVER_NAME);

  } else {
    $errors = $result->getErrorMessages();
    //var_dump($errors);
  }
}else{
  CAdminMessage::ShowNote(Loc::getMessage('API_DATA_ALREADY_INSTALL_MESSAGE'));
}

echo CAdminMessage::ShowNote(Loc::getMessage('API_INSTALL_MESSAGE'));