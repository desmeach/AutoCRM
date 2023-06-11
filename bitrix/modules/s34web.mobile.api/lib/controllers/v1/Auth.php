<?php
namespace s34web\Mobile\Api\controllers\v1;

use s34web\Mobile\Api\controllers\v1\classes\cAuth;
use s34web\Mobile\Api\controllers\v1\classes\cErrors;
use s34web\Mobile\Api\controllers\v1\classes\cGeneral;
use s34web\Mobile\Api\controllers\v1\classes\ResultData;

include(__DIR__ . "/classes/ResultData.php");
include(__DIR__ . "/classes/cGeneral.php");
include(__DIR__ . "/classes/cErrors.php");
include(__DIR__ . "/classes/cAuth.php");

/**
 * Подсистема Авторизация
 *
 * Версия модуля: 1.0.2
 *
 * Разработчик: студия 34web
 *
 * Поддержка: alex@34web.ru
 *
 * @package s34web\Mobile\Api\controllers\v1
 */

class Auth
{
    const IS_TEST_MODE = true;

    //https://manual.phpdoc.org/HTMLSmartyConverter/HandS/phpDocumentor/tutorial_tags.example.pkg.html
    // AUTH METHODS

    /**
     * Метод для регистрации устройств пользователей.
     *
     * TestMode=true
     * Отправка смс не происходит, код всегда 12345.
     *
     * На указанный номер телефона отправляется смс с коротким кодом (5 цифр) и пользователь с указанным device_token регистрируется в системе.
     * Если он был ранее зарегистрирован, ему будет выслано повторное сообщение для входа
     * В данный момент ограничений на отправку нет.
     *
     * https://site.ru/api/v1/auth/register/?phone=79610829950&device_token=aaaaaaaasdf23ff23fa123vas
     *
     * **Внимание! Разрешено использовать только метод POST**
     *
     * Входные параметры
     * ```
     * phone - номер телефона пользователя (обязательный параметр)
     * ```
     *
     * Параметры в заголовке запроса header
     * ```
     * X-MOBILE-DEVICE-ID: (device_token) - токен устройства, уникальный ид устройства для пользователя.
     * ```
     * @TODO: v2. Добавить очистку не активных пользователей, не подтвердивших код из смс по планировщику
     * @TODO: v2. Добавить проверку валидности device_token
     * @TODO: v2. Добавить проверку действительности выданного токена перед его обновлением?
     * @version 1.0
     */
    /**
     * @OA\Post(
     *     path="/api/v1/auth/register/",
     *     summary="Метод для регистрации устройств пользователей.",
     *     description="На указанный номер телефона отправляется смс с коротким кодом (5 цифр) и пользователь с указанным device_token регистрируется в системе.
           Если он был ранее зарегистрирован, ему будет выслано повторное сообщение для входа.
           В данный момент ограничений на отправку нет.
           ",
     *     @OA\RequestBody(
     *      @OA\MediaType(
     *              mediaType="multipart/form-data",
     *              @OA\Schema(
     *                 required={"phone"},
     *                 @OA\Property(property="phone", type="string",  description="номер телефона пользователя", example="79601234567"),
     *              ),
     *      ),
     *      @OA\JsonContent(
     *       required={"phone"},
     *       @OA\Property(property="phone", type="string",  description="номер телефона пользователя", example="79601234567"),
     *      ),
     *     ),
     *     @OA\Parameter (
     *          name="X-MOBILE-DEVICE-ID",
     *          in="header",
     *          required=true,
     *          description="токен устройства, уникальный ид устройства для пользователя",
     *          @OA\Schema(type="string"),
     *     ),
     *     tags={"Авторизация"},
     *     @OA\Response(
     *          response="200",
     *          description="Успешный запрос на регистрацию устройства",
     *          @OA\JsonContent(ref="#/components/schemas/SuccessModel")
     *     ),
     *     @OA\Response(
     *      response="400",
     *      description="Информация об ошибках регистрации, будет доступен список ошибок.

            Список кодов ошибок:
            -1 - Нетипизированная ошибка, смотрите текст сообщения
            49 - Некорректно заполнены обязательные параметры: [список полей запроса]
            113 - Ошибка регистрации
     ",
     *          @OA\JsonContent(ref="#/components/schemas/ErrorModel")
     *     ),
     * )
     */
    public function register()
    {
        $arRequest = cGeneral::getRequest([
            "phone"=>"phone_simle",
            "device_token"
        ]);

        if (cGeneral::checkMethodRequire($arRequest))
        {
            $phone = $arRequest["parameters"]["phone"];
            $device_token = $arRequest["parameters"]["device_token"];

            $arResult = cAuth::register($phone, $device_token);
            cGeneral::sendResponse($arResult,$arRequest);
        }
    }

    /**
     * Метод активации устройства пользователя
     *
     * TestMode=true
     * Отправка смс не происходит, код всегда 12345.
     * Действие токена увеличено до суток.
     *
     * Пользователь с указанным номером телефона и device_token активируется в системе.
     *
     * Время действия кода ограничено 5 минутами.
     *
     * Если истекает срок действия или код неверный выводится ошибка "Некорректный код"
     *
     * После активации пользователю будет отправлены:
     *
     *  - token - токен пользователя, действующий 1 час. Для закрытых методов необходимо передавать его в заголовке X-AUTH-TOKEN.
     *
     *  - refresh_token - дополнительный токен для продления token, в случае если запрос выдал ошибку продолжительности токена 401,
     * необходимо отправить его для продления доступа вместе с основным token в запросе на метод /auth/refresh. Можно продлять в любой момент. Требуется передавать в заголовке X-AUTH-REFRESH-TOKEN.
     *
     * https://site.ru/api/v1/auth/activate/?phone=79610829950&confirm_code=12345&device_token=aaaaaaaasdf23ff23fa123vas
     *
     * **Внимание! Разрешено использовать только метод POST**
     *
     *
     * Входные параметры:
     * ```
     * phone - телефон пользователя в формате +79001234567(обязательный параметр)
     * confirm_code - код подтверждения телефона, полученный после отправки данных при регистрации (обязательный параметр).
     * Код подтверждения является одноразовым, при успешной активации код становится не актуальным.
     * ```
     *
     * Параметры в заголовке запроса header:
     * ```
     * X-MOBILE-DEVICE-ID (device_token) - токен устройства, уникальный ид устройства для пользователя.
     * ```
     *
     * Результат:
     *
     * ```
     * token - токен пользователя (jwt).
     * refresh_token - дополнительный токен для продления основного token.
     * ```
     *
     * @version 1.0
     */
    /**
     * @OA\Post(
     *     path="/api/v1/auth/activate/",
     *     summary="Метод активации устройства пользователя.",
     *     description="Пользователь с указанным номером телефона и device_token активируется в системе.<br>
      Действие токена увеличено до суток.<br>
      Время действия кода ограничено 5 минутами.<br>
      Если истекает срок действия или код неверный выводится ошибка Некорректный код.<br>
      После активации пользователю будет отправлены:

       - token - токен пользователя, действующий 1 час. Для закрытых методов необходимо передавать его в заголовке X-AUTH-TOKEN.
       - refresh_token - дополнительный токен для продления token, в случае если запрос выдал ошибку продолжительности токена 401,
      необходимо отправить его для продления доступа вместе с основным token в запросе на метод /auth/refresh. Можно продлять в любой момент.
     Требуется передавать в заголовке X-AUTH-REFRESH-TOKEN.
    ",
     *     @OA\RequestBody (
     *      @OA\JsonContent (
     *       required={"phone","confirm_code"},
     *       @OA\Property(property="phone", type="string",  description="номер телефона пользователя", example="79601234567"),
     *       @OA\Property(property="confirm_code", type="string",  description="код подтверждения телефона, полученный после отправки данных при регистрации", example=""),
     *      ),
     *     ),
     *     @OA\Parameter (
     *          name="X-MOBILE-DEVICE-ID",
     *          in="header",
     *          required=true,
     *          description="токен устройства, уникальный ид устройства для пользователя",
     *          @OA\Schema(type="string"),
     *     ),
     *     tags={"Авторизация"},
     *     @OA\Response(
     *      response="200",
     *      description="Успешная активация устройства",
     *      @OA\JsonContent (
     *       required={"token","refresh_token"},
     *       @OA\Property(property="token", type="string",  description="номер телефона пользователя", example="eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzM4NCJ9.eyJ1c2VyX2lkIjoyNSwiYXV0aF9pZCI6MiwiZGV2aWNlX3Rva2VuIjoiYWFhYWFhYWFzZGYyM2ZmMjNmYTEyM3ZhcyIsImV4cCI6MTYxMDQ2MDE2MX0.blLpxY3Djnyphj72HnV6kEstzL4kTcCYHIyaVH9lSsDP05GYtIZ9GQMQQFakj2YD"),
     *       @OA\Property(property="refresh_token", type="string",  description="код подтверждения телефона, полученный после отправки данных при регистрации", example="072da9b3684984532a4e0626992a023dakj2YD"),
     *      ),
     *     ),
     *     @OA\Response(
     *      response="400",
     *      description="Информация об ошибках активации устройства, будет доступен список ошибок.

            Список кодов ошибок:
            -1 - Нетипизированная ошибка, смотрите текст сообщения
            12 - Телефон задан некорректно. Допустимый формат - 79601234567
            16 - Не задан код из смс
            18 - Не задан токен устройства
            20 - Пользователь с такими даннами не был найден. Возможно входные параметры неверны
            23 - Код активации приложения не активен
            49 - Некорректно заполнены обязательные параметры: [список полей запроса]
     ",
     *      @OA\JsonContent(ref="#/components/schemas/ErrorModel")
     *     ),
     * )
     *
     * ref="#/components/responses/success",
     */
    public function activate()
    {
        $arRequest = cGeneral::getRequest([
            "phone"=>"phone_simle",
            "confirm_code"
                                         ]);
        if(cGeneral::checkMethodRequire($arRequest))
        {
            $phone = $arRequest["parameters"]["phone"];
            $confirm_code = $arRequest["parameters"]["confirm_code"];
            $device_token = $arRequest["parameters"]["device_token"];
            $arResult = cAuth::activate($phone, $confirm_code, $device_token);
            cGeneral::sendResponse($arResult,$arRequest);
        }
    }

    /**
     *  Метод продления токена пользователя
     *
     *  https://site.ru/api/v1/auth/refresh/?token=eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzM4NCJ9.eyJ1c2VyX2lkIjoyNSwiYXV0aF9pZCI6MiwiZGV2aWNlX3Rva2VuIjoiYWFhYWFhYWFzZGYyM2ZmMjNmYTEyM3ZhcyIsImV4cCI6MTYxMDQ2MDE2MX0.blLpxY3Djnyphj72HnV6kEstzL4kTcCYHIyaVH9lSsDP05GYtIZ9GQMQQFakj2YD&refresh_token=a159e4686650ed77563fb804efcc7df05DerxTa
     *
     * **Внимание! Разрешено использовать только метод POST**
     *
     *  Входные параметры:
     *  Параметры в заголовке запроса header
     *  ```
     *  X-AUTH-TOKEN (token) - токен пользователя (jwt).
     *  X-AUTH-REFRESH-TOKEN (refresh_token) - дополнительный долгоживущий токен для продления основного токена.
     *  ```
     *
     *  Результат ответа в случае успеха:
     *  ```
     *  {
     *   "token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzM4NCJ9.eyJ1c2VyX2lkIjoyNSwiYXV0aF9pZCI6MiwiZGV2aWNlX3Rva2VuIjoiYWFhYWFhYWFzZGYyM2ZmMjNmYTEyM3ZhcyIsImV4cCI6MTYxMDQ2MDE2MX0.blLpxY3Djnyphj72HnV6kEstzL4kTcCYHIyaVH9lSsDP05GYtIZ9GQMQQFakj2YD",
     *   "refresh_token": "072da9b3684984532a4e0626992a023dakj2YD"
     *  }
     *  ```
     *
     *  В случае ошибки будет выдан ответ в виде кода 401 с текстом ошибки.
     *
     * @version 1.0
     */
    /**
     * @OA\Post(
     *     path="/api/v1/auth/refresh/",
     *     summary="Метод продления токена пользователя.",
     *     description="Токен пользователя X-AUTH-TOKEN (jwt) будет обновлён с учётом новой даты активности.",
     *     @OA\Parameter (
     *          name="X-AUTH-TOKEN",
     *          in="header",
     *          required=true,
     *          description="основной токен, который необходимо продлить",
     *          @OA\Schema(type="string"),
     *     ),
     *     @OA\Parameter (
     *          name="X-AUTH-REFRESH-TOKEN",
     *          in="header",
     *          required=true,
     *          description="дополнительный долгоживущий токен, необходим для продления основного токена",
     *          @OA\Schema(type="string"),
     *     ),
     *     tags={"Авторизация"},
     *     @OA\Response(
     *      response="200",
     *      description="Возвращает массив из двух токенов: новый token (jwt) и refresh_token.",
     *      @OA\JsonContent(
     *          @OA\Property(property="token", type="string", example="eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzM4NCJ9.eyJ1c2VyX2lkIjoyNSwiYXV0aF9pZCI6MiwiZGV2aWNlX3Rva2VuIjoiYWFhYWFhYWFzZGYyM2ZmMjNmYTEyM3ZhcyIsImV4cCI6MTYxMDQ2MDE2MX0.blLpxY3Djnyphj72HnV6kEstzL4kTcCYHIyaVH9lSsDP05GYtIZ9GQMQQFakj2YD"),
     *          @OA\Property(property="refresh_token", type="string", example="072da9b3684984532a4e0626992a023dakj2YD"),
     *      ),
     *     ),
     *     @OA\Response(
     *      response="400",
     *      description="Информация об ошибках продления токена

            Список кодов ошибок:
            -1 - Нетипизированная ошибка, смотрите текст сообщения
            41 - Пользователь с таким token не зарегистрирован
            110 - Ошибка продления токена
            111 - Ошибка структуры токена
     ",
     *      @OA\JsonContent(ref="#/components/schemas/ErrorModel")
     *     ),
     * )
     */
    public function refresh()
    {
      $arRequest = cGeneral::getRequest(
        [
          "token",
          "refresh_token"
        ]
      );

      if(cGeneral::checkMethodRequire($arRequest))
      {
        $arResult = cAuth::refreshToken($arRequest["parameters"]["token"], $arRequest["parameters"]["refresh_token"]);
        cGeneral::sendResponse($arResult,$arRequest);
      }
    }

    /**
     * Метод для повторной отправки кода смс на устройство пользователя.
     *
     * TestMode=true
     * Отправка смс не происходит, код всегда 12345.
     *
     * Метод ограничивает кол-во запросов от одного пользователя (1 запрос в 30 секунд).
     *
     * https://site.ru/api/v1/auth/resendCode/?phone=79610829950&device_token=aaaaaaaasdf23ff23fa123vas
     *
     * **Внимание! Разрешено использовать только метод POST**
     *
     * Входные параметры
     * ```
     * phone - телефон пользователя в формате 79001234567 (обязательный параметр)
     * ```
     *
     * Параметры в заголовке запроса header
     * ```
     * X-MOBILE-DEVICE-ID (device_token) - токен устройства, уникальный ид устройства для пользователя.
     * ```
     *
     * @version 1.0
     */
    /**
     * @OA\Post(
     *     path="/api/v1/auth/resendCode/",
     *     summary="Метод для повторной отправки кода смс на устройство пользователя.",
     *     description="Метод ограничивает кол-во запросов от одного пользователя (1 запрос в 30 секунд).",
     *     @OA\RequestBody (
     *      @OA\JsonContent (
     *       required={"phone"},
     *       @OA\Property(property="phone", type="string",  description="номер телефона пользователя", example="79601234567"),
     *      ),
     *     ),
     *     @OA\Parameter (
     *          name="X-MOBILE-DEVICE-ID",
     *          in="header",
     *          required=true,
     *          description="токен устройства, уникальный ид устройства для пользователя",
     *          @OA\Schema(type="string"),
     *     ),
     *     tags={"Авторизация"},
     *     @OA\Response(
     *      response="200",
     *      description="Успешная отправка смс с кодом регистрации",
     *      @OA\JsonContent(ref="#/components/schemas/SuccessModel")
     *     ),
     *     @OA\Response(
     *      response="400",
     *      description="Информация об ошибках продления токена

        Список кодов ошибок:
        -1 - Нетипизированная ошибка, смотрите текст сообщения
        12 - Телефон задан некорректно. Допустимый формат - 79999999999
        51 - Повторная отправка кода доступна через 30 секунд
        52 - Пользователь с таким телефоном не зарегистрирован
        56 - Устройство для пользователя не зарегистрировано
        113 - Ошибка регистрации пользователя
     ",
     *      @OA\JsonContent(ref="#/components/schemas/ErrorModel")
     *     ),
     * )
     */
    public function resendCode()
    {
        $arRequest = cGeneral::getRequest(
            [
                "phone"=>"phone"
            ]
        );
        if(cGeneral::checkMethodRequire($arRequest)) {
            $phone = $arRequest["parameters"]["phone"];
            $device_token = $arRequest["parameters"]["device_token"];
            $arResult = cAuth::resendCode($phone, $device_token);
            cGeneral::sendResponse($arResult,$arRequest);
        }
    }

    /**
     * Метод завершения авторизации на данном устройстве пользователя.
     *
     * Происходит удаление записи об авторизации данного пользователя.
     *
     * **Закрытый метод**
     *
     * https://site.ru/api/v1/auth/logout/?token=eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzM4NCJ9.eyJ1c2VyX2lkIjoyNSwiYXV0aF9pZCI6MiwiZGV2aWNlX3Rva2VuIjoiYWFhYWFhYWFzZGYyM2ZmMjNmYTEyM3ZhcyIsImV4cCI6MTYxMDQ2MDE2MX0.blLpxY3Djnyphj72HnV6kEstzL4kTcCYHIyaVH9lSsDP05GYtIZ9GQMQQFakj2YD
     *
     * **Внимание! Разрешено использовать только метод POST**
     *
     * Параметры в заголовке запроса header
     * ```
     * X-AUTH-TOKEN (token) - токен пользователя (jwt).
     * ```
     *
     * @version 1.0
     */
    /**
     * @OA\Post(
     *     path="/api/v1/auth/logout/",
     *     security={{"apiKeyAuth": {   }}},
     *     summary="Метод завершения авторизации на данном устройстве пользователя.",
     *     description="Происходит удаление записи об авторизации данного пользователя.",
     *     tags={"Авторизация"},
     *     @OA\Response(
     *      response="200",
     *      description="Успешный выход из приложения",
     *      @OA\JsonContent(ref="#/components/schemas/SuccessModel")
     *     ),
     *     @OA\Response(
     *      response="400",
     *      description="Информация об ошибках отмены авторизации, будет доступен список ошибок.

           Список кодов ошибок:
           -1 - Нетипизированная ошибка, смотрите текст сообщения
           5 - Не передан токен пользователя
           111 - Ошибка структуры токена
           18 - Не задан токен устройства
           20 - Пользователь с такими даннами не был найден. Возможно входные параметры неверны
           27 - Выход из приложения уже был произведён
     ",
     *      @OA\JsonContent(ref="#/components/schemas/ErrorModel")
     *     ),
     * )
     */
    public function logout()
    {
        $arRequest = cGeneral::getRequestPersonal();
        if(cGeneral::checkMethodRequire($arRequest))
        {
            $device_token = $arRequest["parameters"]["device_token"];
            $user_id = $arRequest["user_id"];
            $arResult = cAuth::logout($device_token, $user_id);
            //$arResult->setStatusNotRelease();
            cGeneral::sendResponse($arResult,$arRequest);
        }
    }

}