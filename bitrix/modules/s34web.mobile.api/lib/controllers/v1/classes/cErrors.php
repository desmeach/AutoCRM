<?php
namespace s34web\Mobile\Api\controllers\v1\classes;

/**
 * Класс описывает основные ошибки, возникающие при работе с API
 *
 * @package s34web\Mobile\Api\controllers\v1\classes
 */
class cErrors
{
  const INCORRECT_DATE = 4;
  const NO_TOKEN = 5;
  const NO_TYPE_AND_ID = 6;
  const INCORRECT_TYPE = 7;
  const EXIST_ID = 8;
  const NOT_EXIST_ID = 9;
  const INCORRECT_ID = 10;
  const NO_PHONE = 11;
  const INCORRECT_PHONE = 12;
  const EXIST_EMAIL = 13;
  const NO_PASSWORD = 14;
  const INCORRECT_PASSWORD = 15;
  const NO_CODE = 16;

  const NO_DEVICE_TOKEN = 18;
  const ERROR_PASSWORD = 19;
  const INCORRECT_DATA = 20;
  const EXIST_TOKEN = 21;
  const NO_AUTORIZATION = 22;

  const INCORRECT_CODE = 23;
  const ACTIVE_ACCOUNT = 24;
  const EXIST_DEVICE_TOKEN = 25;
  const UPDATE_USER_ERROR = 26;
  const ALREADY_LOG_OUT = 27;
  const NOT_EXIST_EMAIL = 28;
  const NO_OLD_PASSWORD = 29;
  const NO_NEW_PASSWORD = 30;
  const EQUAL_PASSWORDS = 31;
  const NO_USER_WITH_THIS_EMAIL = 36;
  const INCORRECT_ACTIVE = 38;
  const NO_USER_WITH_THIS_TOKEN = 41;
  const NO_DELETE_USER = 43;
  const NO_ACTIVE_ACCOUNT = 47;
  const NOT_EXIST_FAVORITE_ID = 48;
  const INCORRECT_REQUIRE_PARAMS = 49;
  const MINIMUM_LENGHT_PARAMS = 50;
  const NO_TIMEOUT_RETRY_SMS = 51;
  const NO_USER_WITH_THIS_PHONE = 52;
  const NO_EMAIL = 53;
  const INCORRECT_EMAIL = 54;
  const TEST_MODE_PARASM_NO_ACCESS = 55;
  const NO_USER_WITH_THIS_DEVICE = 56;
  const INCORRECT_TIMESTAMP = 57;
  const BIRTHDAY_UPDATE_LOCKED = 58;
  const INCORRECT_DATE_FORMAT = 59;
  const INCORRECT_NAME_DATA = 60;

  const NO_DATA = 101;

  const UPDATE_ERROR = 103;
  const ADD_ERROR = 104;
  const SESSION_TIME = 105;
  const SOCIAL_TOKEN_EXPTIME = 106;
  const TOKEN_REFRESH_ERROR = 110;
  const TOKEN_FORMAT_ERROR = 111;
  const TOKEN_ERROR = 112;
  const REGISTER_ERROR = 113;

  const ORDER_CANCELED_ERROR = 20001;
  const ORDER_NOT_FOUND = 20002;
  const ORDER_CANCELED_ERROR_IS_ALREADY_PAID = 20003;
  const ORDER_CANCELED_ERROR_IS_ALREADY_SHIPPED = 20004;
  const ORDER_CANCELED_ERROR_IS_ALREADY_CANCELED = 20005;
  const ORDERID_IS_INCORRECT = 20006;
  const ORDER_DELIVERY_ERROR = 20100;
  const ORDER_PAYMENT_ERROR = 20101;
  const ORDER_CREATE_NO_ADDRESS = 20102;
  const ORDER_CREATE_NO_STORE_ID = 20103;
  const ORDER_DELIVERY_COLLISION = 20104;

  const ORDER_SAVE_ERROR = 20105;
  const ORDER_TIME_DELIVERY_INTERVAL_INCORRECT = 20106;
  const ORDER_PAYMENT_IS_PAYED = 20107;
  const ORDER_PAYMENT_IS_NOT_ALLOW = 20108;
  const ORDER_GATEWAY_ERROR = 20109;
  const ORDER_NO_AVAIL_DELIVERY = 20110;

  const MODULE_IBLOCK_NOT_LOADED = 201;
  const MODULE_HIGHLOADBLOCK_NOT_LOADED = 202;
  const MODULE_MAIN_NOT_LOADED = 203;
  const MODULE_SALE_NOT_LOADED = 204;
  const NOTFOUND_HIGHLOADBLOCK = 210;
  const INCORRECT_HIGHLOADBLOCK = 211;

  const NO_IBLOCK_ID = 212;
  const NO_ID = 213;
  const NO_FUNCTION = 214;

  const INCORRECT_METHOD = 10001;
  const UNKNOWN_ERROR = 10002;
  const NO_ACCESSBYSITE = 10003;
  const NO_TRADES_4_ORDER = 10004;
  const NO_TRADES_IN_CART = 10005;
  const LOCATION_CODE_INCORRECT = 10006;
  const ORDER_DELIVERY_CALCULATE_ERROR = 10007;

  private static $ErrorList = [
        //Заказы
        self::ORDER_CANCELED_ERROR => 'Ошибка отмены заказа',
        self::ORDER_NOT_FOUND => 'Заказ не найден',
        self::ORDER_CANCELED_ERROR_IS_ALREADY_PAID => 'Заказ уже оплачен и не может быть отменён',
        self::ORDER_CANCELED_ERROR_IS_ALREADY_SHIPPED => 'Заказ уже доставлен и не может быть отменён',
        self::ORDER_CANCELED_ERROR_IS_ALREADY_CANCELED => 'Заказ уже был отменён',
        self::ORDER_DELIVERY_ERROR => 'Передан некорректный id доставки',
        self::ORDER_PAYMENT_ERROR => 'Передан некорректный id оплаты',
        self::ORDER_GATEWAY_ERROR => 'Оплата временно недоступна',
        self::ORDER_CREATE_NO_ADDRESS => 'Для доставки обязателено нужно заполнить поле адрес',
        self::ORDER_CREATE_NO_STORE_ID => 'Для самовывоза нужно обязательно выбрать пункт самовывоза',
        self::ORDER_DELIVERY_COLLISION => 'Для самовывоза нужно обязательно выбрать пункт самовывоза и не указывать адрес доставки, для остальных доставок нужно указать адрес доставки и не указывать пункт самовывоза',
        self::ORDER_NO_AVAIL_DELIVERY => 'Нет доступных доставок',
        self::ORDER_DELIVERY_CALCULATE_ERROR => 'Ошибка расчёта доставки',
        self::ORDER_SAVE_ERROR => 'Ошибка сохранения заказа',
        self::ORDER_TIME_DELIVERY_INTERVAL_INCORRECT => 'Указан не корректный id интервал time_id',
        self::ORDER_PAYMENT_IS_PAYED => 'Заказ уже оплачен',
        self::ORDER_PAYMENT_IS_NOT_ALLOW => 'Оплата в данный момент не разрешена',

        self::NO_ID => 'Не задан id элемента',
        self::NO_IBLOCK_ID => 'Не задан id инфоблока',
        self::NO_FUNCTION => 'Не задана функция для кеширования',
        self::INCORRECT_DATE => 'Дата передана в неверном формате',
        self::LOCATION_CODE_INCORRECT => 'Некорректный код местоположения',

        self::NO_TYPE_AND_ID => 'Необходимо передать корректный тип и идентификатор',
        self::EXIST_ID => 'Указанный идентификатор уже есть в избранном',
        self::NOT_EXIST_FAVORITE_ID => 'Указанный идентификатор не найден в избранном',
        self::NOT_EXIST_ID => 'Указанный идентификатор не найден',
        self::INCORRECT_ID => 'Недопустимый идентификатор',
        self::NO_EMAIL => 'Не задан email',
        self::INCORRECT_EMAIL => 'Email задан некорректно. Допустимый формат - someone@example.com',
        self::INCORRECT_PHONE => 'Телефон задан некорректно. Допустимый формат - 79999999999',
        self::EXIST_EMAIL => 'Пользователь с таким email уже зарегистрирован',
        self::NO_USER_WITH_THIS_EMAIL => 'Пользователь с таким email не зарегистрирован',
        self::NO_USER_WITH_THIS_PHONE => 'Пользователь с таким телефоном не зарегистрирован',
        self::NO_TOKEN => 'Не передан token пользователя',
        self::TOKEN_REFRESH_ERROR => 'Ошибка продления токена',
        self::TOKEN_FORMAT_ERROR => 'Ошибка структуры токена',
        self::NO_DEVICE_TOKEN => 'Не задан токен устройства',
        self::NO_USER_WITH_THIS_TOKEN => 'Пользователь с таким token не зарегистрирован',
        self::NO_PASSWORD => 'Не задан пароль',
        self::INCORRECT_PASSWORD => 'Пароль должен  быть не менее 6 символов длиной.',
        self::NO_CODE => 'Не задан код из смс.',
        //self::NO_PLATFORM => 'Не задан идентификатор платформы.',

        self::ERROR_PASSWORD => 'Указан неверный пароль',
        self::INCORRECT_DATA => 'Пользователь с такими даннами не был найден. Возможно входные параметры неверны',
        self::INCORRECT_NAME_DATA => 'Обязательное одно из трёх: second_name, name, last_name',
        self::NO_AUTORIZATION => 'Авторизация не удалась',
        self::INCORRECT_CODE => 'Код активации приложения не активен',
        self::ACTIVE_ACCOUNT => 'Аккаунт уже активирован',
        self::NO_ACTIVE_ACCOUNT => 'Аккаунт не активирован',
        //self::EXIST_DEVICE_TOKEN => 'Устройство уже авторизовано',
        self::UPDATE_USER_ERROR => 'При обновлении данных пользователя произошла ошибка',
        self::ALREADY_LOG_OUT => 'Выход из приложения уже был произведён',
        self::NOT_EXIST_EMAIL => 'При попытке сбросить пароль произошла ошибка',
        self::NO_OLD_PASSWORD => 'Не задан старый пароль',
        self::NO_NEW_PASSWORD => 'Не задан новый пароль',
        self::EQUAL_PASSWORDS => 'Пароли должны отличаться',
        //self::NO_SOCIAL_TYPE => 'Указан неверный тип социальной сети. Доступные типы - fb, vk',
        //self::NO_SOCIAL_TOKEN => 'Не указан токен доступа к социальной сети',
        //self::NO_EMAIL_HASH => 'Не задан хеш email',
        //self::EXIST_TOKEN_IN_FAVORITE => 'Запись в таблице избранного уже создана для этого токена',
        //self::NO_SUBSCRIBE => 'Категорий подписок у данного пользователя не найдено',
        self::INCORRECT_ACTIVE => 'Значение active должно быть true или false',
        //self::INCORRECT_ACTIVATE_AND_DEACTIVATE => 'activate и deactivate должны быть массивами индексов категорий подписок',
        //self::NO_EXIST_SUBSCRIBE => 'Справочник категорий подписок пуст',
        self::INCORRECT_HIGHLOADBLOCK => 'Некоректный Ид справочника, обратитесь к разработчикам',
        self::NO_DELETE_USER => 'Пользователя с таким телефоном в группе пользователей мобильного приложения не найдено',
        //self::NO_NEWS_THIS_TYPE => 'Новостей данного типа нет',
        //self::INCORRECT_TYPE_NEWS => 'Недопустимое значение типа. Допустимые: news, sale, collection, stock',
        //self::INCORRECT_SOCIAL_TOKEN => 'Некорректный токен социальной сети',
        //self::COMPETING_ID => 'Некорректный запрос, массивы активируемых и деактивируемых подписок не должны пересекаться',

        self::NO_DATA => 'Данные не были получены, возможно входные параметры неверны',       
        //self::NO_MAP_DATA => 'Данные карты не были инициализированы',
        self::UPDATE_ERROR => 'При обновлении highloadblock произошла ошибка',
        self::ADD_ERROR => 'При добалении записи в highloadblock произошла ошибка',
        self::SESSION_TIME => 'Следующий запрос на сброс пароля возможен не ранее чем через 5 минут',
        self::SOCIAL_TOKEN_EXPTIME => 'Токен не действителен',

        self::MODULE_IBLOCK_NOT_LOADED => 'Не был загружен модуль iblock',
        self::MODULE_HIGHLOADBLOCK_NOT_LOADED => 'Не был загружен модуль highloadblock',
        self::MODULE_MAIN_NOT_LOADED => 'Модуль для работы API не был загружен',

        self::INCORRECT_METHOD => 'Некорректный метод',
        self::UNKNOWN_ERROR => 'Неизвестная ошибка',
        self::NO_ACCESSBYSITE => 'Нет доступа к методу',
        self::INCORRECT_REQUIRE_PARAMS => 'Некорректно заполнены обязательные параметры: #PARAMS#',
        self::MINIMUM_LENGHT_PARAMS => 'Минимальная длина текста #MIN_LENGHT#',
        self::TEST_MODE_PARASM_NO_ACCESS => 'Включен тестовый режим. Данные параметры не доступны.',
        self::NO_TIMEOUT_RETRY_SMS => 'Повторная отправка кода доступна через 30 секунд',
        self::NO_USER_WITH_THIS_DEVICE => 'Устройство для пользователя не зарегистрировано',
        self::NO_TRADES_4_ORDER => 'Нет товаров доступных к покупке',
        self::NO_TRADES_IN_CART => 'Нет товаров в корзине',

        self::INCORRECT_TIMESTAMP => 'Неверно указана временная метка',
        self::BIRTHDAY_UPDATE_LOCKED => 'Дата рождения пользователя была установлена ранее. Обратитесь в службу поддержки для её изменения.',
        self::INCORRECT_DATE_FORMAT => 'Дата передана в неверном формате. Поддерживаемый формат: #FORMAT#',
        self::REGISTER_ERROR => 'Ошибка регистрации пользователя',
        self::ORDERID_IS_INCORRECT => 'Номер заказа указан неверно',

    ];

    public static function extendErrors($ErrorListExt,$module_id)
    {
        if(count($ErrorListExt)>0)
            foreach ($ErrorListExt as $key=>$item)
            {
                self::$ErrorList[$module_id."_".$key] = $item;
            }
    }

    /**
     * @param $code
     * @param array $placeholers
     * @return string
     * @ignore
     */
    public static function getText($code, $placeholers=[]){
        if(!isset(self::$ErrorList[$code])) {
            $code = self::UNKNOWN_ERROR;
            $val = self::$ErrorList[$code];
        }
        else {
            $val = self::$ErrorList[$code];
            if(count($placeholers)>0)
                foreach ($placeholers as $pl_name=>$pl_value) {
                    $val = str_replace("#".$pl_name."#",$pl_value,$val);
                }
        }

		return $val;
    }
}