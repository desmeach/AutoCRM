<?php
namespace s34web\Mobile\Api\controllers\v1\classes;

use Bitrix\Main\Loader;
use Bitrix\Main\Sms\Event;
use Bitrix\Main\Type\DateTime;
use Bitrix\Main\UserPhoneAuthTable;
use CTimeZone;
use CUser;
use Exception;
use Nowakowskir\JWT\Exceptions\InvalidStructureException;
use Nowakowskir\JWT\Exceptions\TokenExpiredException;
use Bitrix\Highloadblock as HL;
use Bitrix\Main\Entity;
use Nowakowskir\JWT\JWT;
use Nowakowskir\JWT\TokenDecoded;
use Nowakowskir\JWT\TokenEncoded;


/**
 * Dev: Alex Rilkov
 *
 * Подсистема Авторизации (AUTH)
 * 1. регистрация или авторизация по телефону (передача номера телефона и характеристик телефона бренд, модель, разрешение)
 * 2. повторная отправка кода (смс отправляет сервер через шлюз смс)
 * 3. проверка кода по телефону
 *
 * Аутентификация по токену JWT
 * 1. На входе метод авторизации получает номер телефона
 * Сервер создаёт временную запись (пару код/телефон) и отправляет смс на телефон.
 * 2. В ответ вызывается метод sendCode() и отправляется телефон (или уникальный код записи полученном на пред этапе) и код подтверждения смс на сервер с информацией о телефоне (разрешение экрана, уникальный ид)
 * Сервер формирует auth-token по технологии JWT (алгоритм HS384) из данных:
 * userid,
 * времени дейтсвия токена
 * refresh-token
 *
 * SECRET_KEY вшит с обоих сторон и им происходит шифрование данных
 * Проверка токена на соответствие: https://jwt.io/
 */

class cAuth
{
    public const HL_AUTH_CODE = 5;
    public static  $headerJWT = ["typ" => "JWT", "alg" => JWT::ALGORITHM_HS384];
    public static  $secretKey = "vsdt17v9vSafvv";
    public static $token_time_except = 24*60;//в минутах по умолчанию 60*24 час
    public static $sms_time_resend = 30;//секунд
    public static $sms_time_except = 5;//минут
    public static $permitted_chars = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    public static $test_mode = false;

    /**
     * Метод регистрации пользователей
     * @TODO: Ограничить количество регистраций устройств
     *
     * @param string $phone "номер телефон"
     * @param string $device_token "токен устройства"
     *
     * @return ResultData "возвращает информацию об успешном статусе регистрации"
     */
    public static function register(string $phone,string $device_token){
        $result = new ResultData();
        $error= false;
        $error_message = false;
        $old_phone = trim($phone);
        if(substr($phone,0,1) != "+")
            $phone="+".trim($phone);
        try {
            if(self::checkPhone($phone))
            {
                // ищем юзера по телефону
                $found_user = self::getUserDataByPhone($phone);
                $rand_code = self::getRandomCodeForSms($phone);
                /*
                 * Если пользователь найден, то обновим ему проверочный код и время действия кода, отправим смс для входа
                 * */
                if($found_user)
                {
                    $ID = (int)$found_user['ID'];
                    //Добавление/обновление записи о коде и времени его действия для повторного входа
                    $res = cAuthTable::AddCode($ID, $phone, $device_token, $rand_code);
                    if($res->isSuccess())
                    {
                        $arSend = self::sendSMS($phone, $rand_code);

                        if (empty($arSend["error_message"])) {
                            $error = 0;
                        } else {
                            $error_message = $arSend["error_message"];
                        }
                    }else{
                        //$error = $res->getErrors();
                        $error = cErrors::REGISTER_ERROR;
                    }
                }else{//Если не найден, то добавим не активного пользователя в базу в группу мобильных пользователей
                    $user = new CUser();
                    $password = self::getPassword();
                    $arFields = [
                        "NAME"              => "",
                        "LAST_NAME"         => "",
                        "SECOND_NAME"       => "",
                        "ADMIN_NOTES"       => "Моб.пользователь_".$phone,
                        "EMAIL"             => $phone."@stimul.tel",
                        "LOGIN"             => "mobile_".$old_phone,
                        "PERSONAL_PHONE"    => cGeneral::formatPhone($phone),//форматирование +7 (961) 082-99-50
                        "PHONE_NUMBER"      => $phone,// формат +79610829950
                        "LID"               => "s1",
                        "ACTIVE"            => "N",
                        "PASSWORD"          => $password,
                        "CONFIRM_PASSWORD"  => $password,
                    ];
                    if(cGeneral::IS_NEED_CHECK_MOBILE_GROUP) {
                      $GROUP_ID = cGeneral::getMobileUserGroupId();
                      if ($GROUP_ID>0) {
                        $arFields["GROUP_ID"][] = $GROUP_ID;
                      }
                    }
                    $ID = $user->Add($arFields);

                    if (intval($ID) > 0){
                        //Добавление устройства в базу для конкретного номера телефона
                        if(cAuthTable::AddCode($ID,$phone, $device_token, $rand_code))
                        {
                            $arSend = self::sendSMS($phone, $rand_code);

                            if (empty($arSend["error_message"])) {
                                $error = 0;
                            } else {
                                $error_message = $arSend["error_message"];
                            }
                        }else{
                            $error = cErrors::REGISTER_ERROR;
                        }
                    }else{
                        $error_message = $user->LAST_ERROR;
                    }
                }
            }else {
                $error = cErrors::INCORRECT_PHONE;
            }
        }catch (Exception  $e){
            $error_message = $e->getMessage();
        }
        if(empty($error) && empty($error_message)){
            $result->setSuccess();
        }else{
            $result->setErrors($error, $error_message);
            cGeneral::dump(["register",$result->getResult()],"general3");
            $result->setStatusAuthError();
        }
        return $result;
    }

    /**
     * Генерация пароля
     * TODO: Более сложная Генерация пароля
     * @return int
     */
    private static function getPassword()
    {
        return mt_rand(100000, 999999);
    }

    /**
     * Генерация кода для смс
     * @return int
     */
    private static function getRandomCodeForSms($phone)
    {
       //Код смс
       $test = in_array($phone, cGeneral::getTestPhone());
       return (!cGeneral::isSendSms()||$test) ? cGeneral::getTestSmsCode() : mt_rand(10000, 99999);
    }

    /**
     * Отправка смс
     * @param string $phone
     * @param int $confirm_code
     * @return array
     */
    private static function sendSMS(string $phone, int $confirm_code)
    {
        if(cGeneral::isSendSms())
        {
            $sms = new Event(
                'SMS_MOBILE_USER_CONFIRM_NUMBER',
                [
                    'LID' => 's1',
                    'USER_PHONE' => $phone,
                    'CODE' => $confirm_code,
                ]
            );
            $sms->setSite('s1');
            $eventSend = $sms->send(true);
            if (!$eventSend->isSuccess()) {
                \Bitrix\Main\Diag\Debug::writeToFile([$phone,$confirm_code,$eventSend->getErrorMessages()],'', '/logs/api/sendsms_errors.log');
                return  ["success"=>false,"error_message"=>$eventSend->getErrorMessages()];
            }
        }else{
            \Bitrix\Main\Diag\Debug::writeToFile([$phone,$confirm_code],'', '/api/logs/sendsms.log');
        }
        return  ["success"=>true];
    }


    /**
     * Метод активации аккуанта пользователя по коду
     *
     * @param $phone "электронная почта пользователя"
     * @param $code "код, полученный в смс после отправки данных при регистрации"
     * @param $device_token  "токен устройства"
     *
     * @return ResultData
     */
    public static function activate($phone, $code, $device_token){
        $result = new ResultData();
        $data  = [];
        $error = false;
        $error_message = false;
        try {
            $phone="+".$phone;
            if(self::checkPhone($phone)){
                if(self::checkCode($code)){
                    if(!empty($device_token)) {
                        $found_user = self::getUserDataByPhone($phone);
                        if ($found_user) {
                            //if ($userFields["ACTIVE"] != "Y") {
                            if ($auth_id = cAuthTable::validateCode((int)$found_user["ID"], $device_token, (string)$code))
                            {
                                $arActivate = self::activateUser($found_user["ID"],$auth_id,$device_token);
                                if (!empty($arActivate["error_message"])) {
                                    $error_message = $arActivate["error_message"];
                                } elseif (!empty($arActivate["error"])) {
                                    $error = $arActivate["error"];
                                } else {
                                    $data["token"] = $arActivate["token"];
                                    $data["refresh_token"] = $arActivate["refresh_token"];
                                    //$data["id"] = $found_user["ID"];
                                    //$data["phone"] = $phone;
                                    //$data["name"] = $userFields["NAME"];
                                }
                            } else {
                                $error = cErrors::INCORRECT_CODE;
                            }
                            /*} else {
                                 $error = Errors::ACTIVE_ACCOUNT;
                             }*/
                        } else {
                            $error = cErrors::INCORRECT_DATA;
                        }
                    }else{
                        $error = cErrors::NO_DEVICE_TOKEN;
                    }
                }else{
                    $error = cErrors::NO_CODE;
                }
            }else{
                $error = cErrors::INCORRECT_PHONE;
            }
        }catch (Exception  $e){
            $error_message = $e->getMessage();
        }
        if(empty($error) && empty($error_message)){
            $result->setData($data);
        }else{
            $result->setErrors($error, $error_message);
            $result->setStatusAuthError();
        }

        return $result;
    }

    /**
     * Метод повторной отправки кода через 30 секунд
     * Реализуется в методе register похожий функционал
     *
     * @param string $phone "номер телефон"
     * @param string $device_token "токен устройста пользователя"
     *
     * @return ResultData "возвращает информацию о статусе отправки кода"
     */
    public static function resendCode(string $phone,string $device_token){
        CTimeZone::Disable();
        $result = new ResultData();
        $error = false;
        $error_message = false;
        try {
            $phone="+".$phone;
            if(self::checkPhone($phone))
            {
                $found_user = self::getUserDataByPhone($phone);
                if($found_user)
                {
                    $arAuthData = cAuthTable::getCode((int)$found_user["ID"], $device_token);
                    if($arAuthData!==false) {
                        //Задержка отправки в n секунд
                        $time = new DateTime($arAuthData["UF_LAST_TIME_SEND"]);
                        $time->add("+" . self::$sms_time_resend . " seconds");
                        if (!cAuthTable::validExpectedTime($time))//время для последующего разрешения рассылки смс
                        {
                            $rand_code = self::getRandomCodeForSms($phone);
                            if (cAuthTable::addCode((int)$found_user["ID"], $phone, $device_token, $rand_code))
                            {
                                $arSend = self::sendSMS($phone, $rand_code);
                                if (empty($arSend["error_message"])) {
                                    $error = 0;
                                } else {
                                    $error_message = $arSend["error_message"];
                                }
                            }else{
                                //$error = $res->getErrors();
                                $error = cErrors::REGISTER_ERROR;
                            }
                        } else {
                            $error = cErrors::NO_TIMEOUT_RETRY_SMS;
                        }
                    }else{
                        $error = cErrors::NO_USER_WITH_THIS_DEVICE;
                    }
                }else{
                    $error = cErrors::NO_USER_WITH_THIS_PHONE;
                }
            }else {
                $error = cErrors::INCORRECT_PHONE;
            }
        }catch (Exception  $e){
            $error_message = $e->getMessage();
        }
        if(empty($error) && empty($error_message)){
            $result->setSuccess();
        }else{
            $result->setStatusAuthError();
            $result->setErrors($error, $error_message);
        }
        return $result;
    }

    private static function activateUser(int $userId, int $auth_id, string $device_token){
        $result = ['token' => "", 'error' => "", 'error_message'=>"Критическая ошибка"];
        if($userId>0 && $auth_id>0) {
            $error = '';
            $error_message = '';
            $result = cAuthTable::ActivateCode($auth_id);
            if(!$result->isSuccess())
            {
                $error_message = "Ошибка активации пользователя";
            }elseif(!$result= self::updateTokens($userId, $auth_id, $device_token)) {
                $error_message = "Ошибка активации пользователя";
            }


            $result['error'] = $error;
            $result['error_message'] = $error_message;
            return $result;
        }else{
            return $result;
        }
    }

    public static function updateTokens(int $userId,int $auth_id,string $device_token)
    {
        if($userId>0 && $auth_id>0) {
            $ctoken = self::createToken($userId, $auth_id, $device_token);
            $token = $ctoken->toString();
            $refresh_token = self::createRefreshToken($token);

            if(!(new CUser())->Update($userId, ["ACTIVE" => "Y"]))
            {
                return false;
            }
            $params = [];
            $params["UF_TOKEN"] = $token;//$ctoken->getSing();
            $params["UF_REFRESH_TOKEN"] = $refresh_token;
            $params["UF_REFRESH_TIME"] = new DateTime();

            if (!cAuthTable::Update($auth_id, $params)) {
                return false;
            }

            return ['token' => $token, 'refresh_token' => $refresh_token];
        }else
            return false;
    }

    /**
     * Метод выход из приложения и очистка временных данных
     *
     * @param $device_token "Идентификационный ключ пользовательского устройства"
     * @param $user_id "Идентификатор пользователя в базе"
     *
     * @return ResultData
     */
    public static function logout($device_token, $user_id){
        $result = new ResultData();
        $error= false;
        $error_message = false;
        //General::dump(["logout",$user_id,$device_token],"auth");
        try {
            if(!empty($device_token)){

                    $filter = Array(
                        "=ID" => $user_id,
                    );
                    $param = Array(
                        'FIELDS' => Array(
                            "ID"
                        )
                    );
                    $by="ID";$order="DESC";
                    $userResult = CUser::GetList($by, $order, $filter, $param);
                    if($userFields = $userResult-> Fetch()){
                        //Удаление записи об активации устройства
                        cGeneral::dump(["logout",$user_id,$device_token],"auth");
                        if( cAuthTable::DeleteAuthDataByDeviceId($user_id, $device_token))
                        {
                            /*//Сброс токена пользователя, если нет устройств!!! зачем?
                            $fields = Array(
                                "UF_TOKEN" => ""
                            );
                            if(($user = new \CUser())->Update($userFields["ID"], $fields)){
                                $error = 0;
                            }else{
                                $error_message = $user->LAST_ERROR;
                            }*/
                        }else{
                            $error = cErrors::ALREADY_LOG_OUT;
                        }
                    }else{
                        $error = cErrors::INCORRECT_DATA;
                    }
            }else{
                $error = cErrors::NO_DEVICE_TOKEN;
            }
        }catch (Exception  $e){
            $error_message = $e->getMessage();
        }

        if(empty($error) && empty($error_message)){
            $result->setSuccess();
        }else{

            $result->setErrors($error, $error_message);
            $result->setStatusBadRequestError();
        }
        return $result;
    }

    /**
     * Проверка номера телефона на валидность
     * Формат: +79601111111
     *
     * @param $phone
     * @return bool
     */
    private static function checkPhone($phone)
    {
        return strlen($phone)==12 && preg_match('#^\+[0-9]+$#', $phone);
    }

    private static function checkCode($code)
    {
        return strlen($code)==5 && preg_match('#^[0-9]+$#', $code);
    }

    /**
     * Аутентинтификация по токену методом JWT
     *
     * @param int $user_id
     * @param int $auth_id
     * @param string $device_token
     * @return TokenEncoded
     */
    public static function createToken(int $user_id,int $auth_id,string $device_token)
    {
        $payload = [
            "user_id" => $user_id,
            "auth_id" => $auth_id,
            "device_token" => $device_token,
            "exp" => time() + self::$token_time_except * 60
        ];
        $tokenData = new TokenDecoded($payload, self::$headerJWT);

        try{
            $tokenResult = $tokenData->encode(self::$secretKey,JWT::ALGORITHM_HS384);
        } catch (Exception $e) {
           // echo $e->getMessage();
            return null; //"error ".;
        }

        return $tokenResult;
    }

    /**
     * Создание случайной последовательности символов
     *
     * @param int $strength
     * @return string
     */
    public static function createRandomString($strength = 16)
    {
        $input = self::$permitted_chars;
        $input_length = strlen($input);
        $random_string = '';
        for($i = 0; $i < $strength; $i++) {
            $random_character = $input[mt_rand(0, $input_length - 1)];
            $random_string .= $random_character;
        }

        return $random_string;
    }

    /**
     * Создание случайной последовательности символов 2
     *
     * @param int $strength
     * @return string
     */
    public static function createCryptString($strength = 16)
    {
        return bin2hex(random_bytes($strength));
    }

    public static function createRefreshToken(string $token)
    {
        $base_string = self::createCryptString(16);

        return $base_string.substr($token,-6);
    }

   /**
   * @param string $token
   * @return array | false
   */
    public static function getDataFromToken(string $token)
    {
      try {
        $tokenData = new TokenEncoded($token);
        $data = $tokenData->decode();
        return $data->getPayload();
      }catch (Exception $ex) {
        //print_r($ex->getMessage());
        return false;
      }
    }

    /**
     * @param string $token
     * @return array | false
     * @throws TokenExpiredException
     */
    public static function validateToken(string $token)
    {
        try {
            $tokenData = new TokenEncoded($token);
            $tokenData->validate(self::$secretKey, JWT::ALGORITHM_HS384);
            $data = $tokenData->decode();
            return $data->getPayload();
            //Переотправить событие завершения токена
        } catch (TokenExpiredException $e) {
            throw $e;
        }catch (Exception $ex)
        {
            //print_r($ex->getMessage());
            return  false;
        }
    }

    private static function getUserDataByPhone(string $phone)
    {
        // ищем юзера по телефону
        $phone = UserPhoneAuthTable::normalizePhoneNumber($phone); // нормализуем номер телефона
        $user = UserPhoneAuthTable::getList($parameters = array(
            'filter'=>array('PHONE_NUMBER' =>$phone, /*'CONFIRMED'=>'Y'*/) // выборка пользователя с подтвержденным номером
        ));
        $found_user = false;
        if($row = $user->fetch())
        {
            $rsUser = CUser::GetByID($row['USER_ID']); // найдем пользователя по ID
            $found_user = $rsUser->Fetch();
        }
        return $found_user;
    }

  /**
   * Продление токена
   * @param string $token
   * @param string $refresh_token
   * @return ResultData
   */
  public static function refreshToken(string $token, string $refresh_token)
  {
      $result = new ResultData();
      if(!empty($refresh_token) && !empty($token))
      {

          $payload = cAUTH::getDataFromToken($token);
          if($payload!==false) {
            $user_id = intval($payload["user_id"]);
            $auth_id = intval($payload["auth_id"]);
            $device_token = $payload["device_token"];
            /*$data["parameters"]["device_token"] = $device_token;
            $data["user_id"] = $user_id;*/
            //Проверить корректность сохранённого токена и refresh_token
            $user_auth = cAuthTable::getAuthByID($user_id, $auth_id);
            if ($user_auth["UF_TOKEN"] == $token && $user_auth["UF_REFRESH_TOKEN"] == $refresh_token) {
              if ($tokens_data = cAUTH::updateTokens($user_id, $auth_id, $device_token)) {
                $data = [];
                //$data["is_refresh"] = true;
                $data["token"] = $tokens_data["token"];
                $data["refresh_token"] = $tokens_data["refresh_token"];
                $result->setData($data);
              } else {
                $result->setErrors(cErrors::TOKEN_REFRESH_ERROR);
                $result->setStatusAuthError();
              }
            } else {
              $result->setErrors(cErrors::NO_USER_WITH_THIS_TOKEN);
              $result->setStatusAuthError();
            }
          }else{
            $result->setErrors(cErrors::TOKEN_FORMAT_ERROR);
            $result->setStatusAuthError();
          }


        //Иначе выдадим ошибку продления
      }

    return $result;
  }




}