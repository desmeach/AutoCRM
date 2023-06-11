<?php
$MESS['API_MODULE_ID'] = 's34web.mobile.api';
$MESS['PAGE_TITLE'] = 'Настройки';

$MESS['TAB_MAIN_TITLE'] = 'Настройки';
$MESS['TAB_SECURITY_TITLE'] = 'Безопасность';
$MESS['TAB_SUPPORT_TITLE'] = 'Поддержка';

$MESS['TAB_MAIN_DESCRIPTION'] = 'Основные настройки';
$MESS['TAB_SECURITY_DESCRIPTION'] = 'Настройки безопасности';
$MESS['TAB_SUPPORT_DESCRIPTION'] = 'Поддержка модуля';

$MESS['OPTION_USE_RESTFUL_API'] = 'Использовать RESTful API';
$MESS['OPTION_USE_RESTFUL_API_SELECT_TITLE_1'] = 'Отключено';
$MESS['OPTION_USE_RESTFUL_API_SELECT_TITLE_2'] = 'Включено';
$MESS['OPTION_USE_RESTFUL_API_SELECT_ID_1'] = 'N';
$MESS['OPTION_USE_RESTFUL_API_SELECT_ID_2'] = 'Y';
$MESS['OPTION_USE_RESTFUL_API_SELECT_HINT'] = 'Если параметр включён, тогда будет возможность использовать RESTful API на сайте.';

$MESS['OPTION_PATH_RESTFUL_API'] = 'Путь RESTful API';
$MESS['OPTION_PATH_RESTFUL_HINT'] = 'Путь по которому будет доступен интерфейс.<br><br>Пример: <b>/api/</b>';

$MESS['OPTION_USE_VERSIONS'] = 'Использовать версии';
$MESS['OPTION_USE_VERSIONS_SELECT_TITLE_1'] = 'Отключено';
$MESS['OPTION_USE_VERSIONS_SELECT_TITLE_2'] = 'Включено';
$MESS['OPTION_USE_VERSIONS_SELECT_ID_1'] = 'N';
$MESS['OPTION_USE_VERSIONS_SELECT_ID_2'] = 'Y';
$MESS['OPTION_USE_VERSIONS_SELECT_HINT'] = 'Если параметр включён, тогда будет учитываться версия контроллера обрабатывающего запрос.<br><br>Например: /api/<b>v1</b>/ - в этом случае интерфейс будет подключать контроллер, который располагается по пути: [папка модуля]/lib/controllers/<b>v1</b>/[название контроллера].';


$MESS['OPTION_OPERATING_MODE'] = 'Режим работы';
$MESS['OPTION_OPERATING_MODE_SELECT_TITLE_1'] = 'Объектно-ориентированный (По умолчанию)';
$MESS['OPTION_OPERATING_MODE_SELECT_TITLE_2'] = 'Файловый';
$MESS['OPTION_OPERATING_MODE_SELECT_TITLE_3'] = 'Объектно-ориентированный + Файловый';
$MESS['OPTION_OPERATING_MODE_SELECT_ID_1'] = 'OBJECT_ORIENTED';
$MESS['OPTION_OPERATING_MODE_SELECT_ID_2'] = 'FILE';
$MESS['OPTION_OPERATING_MODE_SELECT_ID_3'] = 'OBJECT_ORIENTED_FILE';
$MESS['OPTION_OPERATING_MODE_HINT'] = 'Объектно-ориентированный - запрос будет обрабатываться соответствующим контроллером (объектом класса) и методом класса, который распологается в папке модуля (см. документацию).<br><br>Файловый - запрос будет обрабатываться контроллером, который располагается по "одноимённому" физическому пути (см. документацию).<br><br>Объектно-ориентированный + Файловый - при запросе будет происходить поиск контроллера в папке модуля, при неудачной же попытке, поиск будет происходить по "одноимённому" физическому пути (см. документацию).';


$MESS['OPTION_USE_ONLY_HTTPS_EXCHANGE_TITLE'] = 'Обмен только через https-протокол';
$MESS['OPTION_ONLY_HTTPS_EXCHANGE_HINT'] = 'Если параметр активен, тогда все запросы, которые приходят на API-интерфейс через http-протокол - будут отклонены.<br><br>Внимание: сайт должен быть доступен по адресу HTTPS://'.$_SERVER['SERVER_NAME'].'.<br><br>Примечание: HTTPS обеспечивает шифрование данных при их передачи.';

$MESS['OPTION_USE_AUTH_TOKEN_TITLE'] = 'Авторизация по токену';
$MESS['OPTION_AUTH_TOKEN_HINT'] = 'Если параметр активен, тогда при запросе будет проверяться наличие пользователя в базе по указанному токену.<br><br>Внимание:<br>1. пользователю будет добавлено пользовательское поле #FIELD_NAME_RESTFUL_API_TOKEN#;<br>2. важно чтобы при запросе со стороны клиента был указан заголовок Authorization-Token с токеном (ключевая фраза<b>:</b>токен пользователя).';

$MESS['OPTION_TOKEN_KEYWORD_TITLE'] = 'Ключевая фраза';
$MESS['OPTION_TOKEN_KEYWORD_HINT'] = 'Ключевая фраза является дополнением к токену пользователя, т.е. {Фраза}<b>:</b>{Токен пользователя}.<br><br>Пример токена: KEYWORD<b>:</b>d7804258-07dd-4216-84f5-3606168ec4d4.';

$MESS['TOKEN_GENERATE_TITLE'] = 'Сгенерировать токены';
$MESS['TOKEN_GENERATE_HINT'] = 'Если включена авторизация по токену, тогда при клике на ссылку будут сгенерированы токены для тех пользователей у кого они отсутствуют.';

$MESS['GENERATE_LINK_TEXT'] = 'Сгенерировать';

$MESS['OPTION_USE_WHITE_LIST_ADDRESS_FILTER_SELECT_TITLE_1'] = 'Отключен';
$MESS['OPTION_USE_WHITE_LIST_ADDRESS_FILTER_SELECT_TITLE_2'] = 'Включен';
$MESS['OPTION_USE_WHITE_LIST_ADDRESS_FILTER_SELECT_ID_1'] = 'N';
$MESS['OPTION_USE_WHITE_LIST_ADDRESS_FILTER_SELECT_ID_2'] = 'Y';
$MESS['OPTION_USE_WHITE_LIST_ADDRESS_FILTER_HINT'] = 'Если параметр включён, тогда при обработке входящего запроса будет проверяться источник запроса.';
$MESS['OPTION_WHITE_LIST_ADDRESS_TITLE'] = 'Список белых адресов';
$MESS['OPTION_WHITE_LIST_ADDRESS_HINT'] = 'Список разрешенных адресов, с которых API-интерфейс будет обрабатывать входящие запросы.<br>Адреса нужно писать через точку с запятой.<br><br>Пример: 192.168.0.1; 192.168.0.2; 192.168.0.3';

$MESS['OPTION_USE_BLACK_LIST_ADDRESS_FILTER'] = 'Использовать фильтр по чёрным IP-адресам';
$MESS['OPTION_USE_BLACK_LIST_ADDRESS_FILTER_SELECT_TITLE_1'] = 'Отключен';
$MESS['OPTION_USE_BLACK_LIST_ADDRESS_FILTER_SELECT_TITLE_2'] = 'Включен';
$MESS['OPTION_USE_BLACK_LIST_ADDRESS_FILTER_SELECT_ID_1'] = 'N';
$MESS['OPTION_USE_BLACK_LIST_ADDRESS_FILTER_SELECT_ID_2'] = 'Y';
$MESS['OPTION_USE_BLACK_LIST_ADDRESS_FILTER_HINT'] = 'Если параметр включён, тогда при обработке входящего запроса будет проверяться источник запроса.';
$MESS['OPTION_BLACK_LIST_ADDRESS_TITLE'] = 'Список чёрных адресов';
$MESS['OPTION_BLACK_LIST_ADDRESS_HINT'] = 'Список запрещенных адресов, с которых API-интерфейс НЕ будет обрабатывать входящие запросы.<br>Адреса нужно писать через точку с запятой.<br><br>Пример: 192.168.0.1; 192.168.0.2; 192.168.0.3';


$MESS['OPTION_USE_LIST_COUNTRY_FILTER'] = 'Использовать фильтр по коду страны';
$MESS['OPTION_USE_LIST_COUNTRY_FILTER_SELECT_TITLE_1'] = 'Отключен';
$MESS['OPTION_USE_LIST_COUNTRY_FILTER_SELECT_TITLE_2'] = 'Включен';
$MESS['OPTION_USE_LIST_COUNTRY_FILTER_SELECT_ID_1'] = 'N';
$MESS['OPTION_USE_LIST_COUNTRY_FILTER_SELECT_ID_2'] = 'Y';
$MESS['OPTION_USE_LIST_COUNTRY_FILTER_HINT'] = 'Если параметр включён, тогда при обработке входящего запроса будет проверяться код страны источника запроса.';
$MESS['OPTION_WHITE_LIST_COUNTRY_TITLE'] = 'Список кодов стран';
$MESS['OPTION_WHITE_LIST_COUNTRY_HINT'] = 'Список разрешенных кодов стран, с которых API-интерфейс будет обрабатывать входящие запросы.<br>Коды нужно писать через точку с запятой.<br><br>Пример: RU; KZ; BY';


$MESS['OPTION_USE_ACCESS_CONTROL_ALLOW_ORIGIN_FILTER'] = 'Кросс-доменные запросы';
$MESS['OPTION_USE_ACCESS_CONTROL_ALLOW_ORIGIN_FILTER_SELECT_TITLE_1'] = 'Отключены';
$MESS['OPTION_USE_ACCESS_CONTROL_ALLOW_ORIGIN_FILTER_SELECT_TITLE_2'] = 'Разрешены';
$MESS['OPTION_USE_ACCESS_CONTROL_ALLOW_ORIGIN_FILTER_SELECT_ID_1'] = 'N';
$MESS['OPTION_USE_ACCESS_CONTROL_ALLOW_ORIGIN_FILTER_SELECT_ID_2'] = 'Y';
$MESS['OPTION_USE_ACCESS_CONTROL_ALLOW_ORIGIN_FILTER_HINT'] = 'Если параметр включён, тогда при обработке входящего запроса будет проверяться домен источника запроса.';
$MESS['OPTION_WHITE_LIST_DOMAIN_ACCESS_CONTROL_ALLOW_ORIGIN_TITLE'] = 'Список доменов';
$MESS['OPTION_WHITE_LIST_DOMAIN_ACCESS_CONTROL_ALLOW_ORIGIN_HINT'] = 'Список разрешенных доменов, с которых API-интерфейс будет обрабатывать входящие запросы.<br>Домены нужно писать через точку с запятой.<br><br>Пример: http://site1.com; http://site2.com<br><br>Можно указать символ Звёздочка (*), в этом случае интерфейс будет доступен всем доменам.';



$MESS['SUPPORT_LINK_TITLE'] = 'Вопросы и предложения по модулю';
$MESS['SUPPORT_LINK'] = 'https://34web.ru';
$MESS['SUPPORT_LINK_TEXT'] = 'Перейти';
$MESS['SUPPORT_LINK_HINT'] = 'Для связи с разработчиком модуля';

$MESS['SUPPORT_DOCUMENTATION_LINK_TITLE'] = 'Документация';
$MESS['SUPPORT_DOCUMENTATION_LINK'] = 'http://api.stimul.tel/';
$MESS['SUPPORT_DOCUMENTATION_LINK_TEXT'] = 'Перейти';
$MESS['SUPPORT_DOCUMENTATION_LINK_HINT'] = 'Описание работы модуля с примерами реализации';

$MESS['SUPPORT_FEEDBACK_LINK_TITLE'] = 'Отзывы';
$MESS['SUPPORT_FEEDBACK_LINK'] = 'mailto:alex@34web.ru';
$MESS['SUPPORT_FEEDBACK_LINK_TEXT'] = 'Перейти';
$MESS['SUPPORT_FEEDBACK_LINK_HINT'] = 'Не забывайте оставлять свои отзывы. У Вас это отнимет всего лишь 1-3 минуты, а для нас это большая мотивация к развитию и улучшению продукта.';

$MESS['OPTION_SUPPORT_USE_LOG_TITLE'] = 'Логирование входящих запросов';
$MESS['OPTION_SUPPORT_USE_LOG_SELECT_TITLE_1'] = 'Отключено';
$MESS['OPTION_SUPPORT_USE_LOG_SELECT_TITLE_2'] = 'Включено';
$MESS['OPTION_SUPPORT_USE_LOG_SELECT_ID_1'] = 'N';
$MESS['OPTION_SUPPORT_USE_LOG_SELECT_ID_2'] = 'Y';
$MESS['OPTION_SUPPORT_USE_LOG_HINT'] = 'Логирование всех входящих запросов в файл';

$MESS['OPTION_SUPPORT_LOG_PATH_TITLE'] = 'Путь до лог-файла';
$MESS['OPTION_SUPPORT_LOG_PATH_HINT'] = 'Путь по которому будет доступен лог-файл.<br><br>Пример: <b>/logs/api.log</b><br><br>Примечание: файл и директория должны быть созданы заранее, а также должны быть настроены права на запись в файл.';


$MESS['BTN_OPTIONS_SAVE'] = 'Сохранить';
$MESS['BTN_OPTIONS_RESTORE'] = 'Сбросить';
$MESS['OPTIONS_SAVED'] = 'Настройки сохранены';
$MESS['OPTIONS_RESTORED'] = 'Настройки сброшены';

$MESS['TOKENS_GENERATED'] = 'Сгенерировано токенов: #COUNT#';