<?php
namespace s34web\Mobile\Api\controllers\v1\classes;
use Bitrix\Highloadblock as HL;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\Loader;
use Bitrix\Main\LoaderException;
use Bitrix\Main\ObjectPropertyException;
use Bitrix\Main\SystemException;
use Bitrix\Main\Type\DateTime;
use CTimeZone;
use Exception;

/**
 * Created: 02.01.2021, 14:06
 * Author : Alex Rilkov <alex@34web.ru>
 * Company: 34web Studio
 */

class cAuthTable{

    const HL_AUTH_CODE = 5;

    private static function GetEntity()
    {
        Loader::includeModule("highloadblock");

        $hlbl = self::HL_AUTH_CODE; // Указываем ID нашего highloadblock блока к которому будет делать запросы.
        $hlblock = HL\HighloadBlockTable::getById($hlbl)->fetch();
        $entity = HL\HighloadBlockTable::compileEntity($hlblock);
        return $entity->getDataClass();
    }

    /**
     * @param int $user_id
     * @param string $phone
     * @param $device_token
     * @param $code
     * @return ResultData
     * @throws Exception
     */
    public static function AddCode(int $user_id, string $phone, $device_token, $code)
    {
        $result = new ResultData();
        try {
            $entity_data_class = self::GetEntity();
            $rsData = $entity_data_class::getList(array(
                                                      "select" => array("ID"),
                                                      "order" => array("ID" => "ASC"),
                                                      "filter" => array(
                                                          "UF_USER_ID" => $user_id,
                                                          "UF_DEVICE_TOKEN" => $device_token
                                                      )  // Задаем параметры фильтра выборки
                                                  ));
            // Массив полей для добавления
            $data = array(
                "UF_USER_ID" => $user_id,
                "UF_PHONE" => $phone,
                "UF_DEVICE_TOKEN" => $device_token,
                "UF_CODE" => $code,
                "UF_TIME_VALID" => self::getExpectedCodeTime(),
                "UF_LAST_TIME_SEND" => self::getCurrentTime()
            );
            if ($arData = $rsData->Fetch()) {
                $res = $entity_data_class::update($arData["ID"], $data);
            } else {
                $res = $entity_data_class::add($data);
            }
            if($res->getErrorMessages())
                cGeneral::dump(["addCode", $res->getErrorMessages()], "general34");
            else
                $result->setSuccess();
            return $result;
        }catch (Exception $ex){
            cGeneral::dump(["register", $ex->getMessage()], "general34");
            $result->setErrors(cErrors::REGISTER_ERROR);
            return $result;
        }
    }

    /**
     *  "UF_CODE","UF_TIME_VALID"
     *
     * @param int $user_id
     * @param string $device_token
     * @return array|bool
     * @throws ArgumentException
     * @throws LoaderException
     * @throws ObjectPropertyException
     * @throws SystemException
     */
    public static function GetCode(int $user_id, string $device_token)
    {
        $entity_data_class = self::GetEntity();
        $rsData = $entity_data_class::getList(array(
                                                  "select" => array("ID","UF_CODE","UF_TIME_VALID","UF_LAST_TIME_SEND"),
                                                  "order" => array("ID" => "ASC"),
                                                  "filter" => array("UF_USER_ID"=>$user_id,"UF_DEVICE_TOKEN"=>$device_token)  // Задаем параметры фильтра выборки
                                              ));

        if($arData = $rsData->Fetch()){
            return $arData;
        }
        else return false;

    }

    public static function Update($auth_id, array $params)
    {
        $entity_data_class = self::GetEntity();
        return $entity_data_class::update($auth_id, $params);
    }

    public static function ActivateCode($auth_id)
    {
        $params = array("UF_CODE" => "--A--");
        $entity_data_class = self::GetEntity();
        return $entity_data_class::update($auth_id, $params);
    }


    /**
     * Проверяет код на соответствие и возвращает ид записи с данными, если проверка пройдена
     *
     * @param int $user_id
     * @param string $device_token
     * @param string $code_for_validate
     * @return int|false
     */
    public static function validateCode(int $user_id, string $device_token, string $code_for_validate)
    {
        if($arCodeData = cAuthTable::getCode($user_id, $device_token))
        {
            if($arCodeData["UF_CODE"] == $code_for_validate)
            {
                if(self::validExpectedTime($arCodeData["UF_TIME_VALID"]))
                    return $arCodeData["ID"];
            }
        }
        return false;
    }

    public static function DeleteAuthDataByDeviceId($user_id, $device_token)
    {
        $entity_data_class = self::GetEntity();
        try {
            $rsData = $entity_data_class::getList(array(
                                                  "select" => array("ID"),
                                                  "order" => array("ID" => "ASC"),
                                                  "filter" => array("UF_USER_ID"=>$user_id,"UF_DEVICE_TOKEN"=>$device_token)  // Задаем параметры фильтра выборки
                                              ));
        }catch (Exception $exception)
        {
            //Логируем не предвиденную ошибку
            cGeneral::dump(["DeleteAuthDataByDeviceId=>getList",$exception->getMessage(),$user_id,$device_token],"auth");
            return false;
        }
        if($arData = $rsData->Fetch()){
            cGeneral::dump([$arData, $user_id, $device_token],"auth_logout");
            try {
               $result= $entity_data_class::delete($arData["ID"]);
               if(!$result->isSuccess())
               {
                 cGeneral::dump(["DeleteAuthDataByDeviceId=>delete",$result->getErrorMessages(),$arData["ID"],$user_id,$device_token],"auth");
                 return false;
               }else {
                 return true;
               }
            }catch (Exception $exception)
            {
                //Логируем не предвиденную ошибку
                cGeneral::dump(["DeleteAuthDataByDeviceId=>delete",$exception->getMessage(),$arData["ID"],$user_id,$device_token],"auth");
                return false;
            }
        }else
            return false;
    }


    /**
     * Получение даты окончания валидности смс кода
     *
     * @return DateTime
     * @throws Exception
     */
    private static function getExpectedCodeTime()
    {
        CTimeZone::Disable();
        $date = self::getCurrentTime();
        $date->add("+".(cAuth::$sms_time_except)." minutes");
        return $date;
    }

    /**
     * Проверка валидности смс кода
     *
     * @param $datetime_prev "Предыдущая дата истечения"
     * @return bool
     */
    public static function validExpectedTime($datetime_prev)
    {
        CTimeZone::Disable();
        $date_now = new DateTime();
        //Если текущая дата старше предыдущей, то время вышло
        return $date_now->getTimestamp()<=$datetime_prev->getTimestamp();
    }

    /**
     * Текущая дата и время
     *
     * @return DateTime
     * @throws Exception
     */
    private static function getCurrentTime()
    {
        CTimeZone::Disable();
        return new DateTime();
    }

    public static function getAuthByID(int $user_id, int $auth_id)
    {
        $entity_data_class = self::GetEntity();
        $rsData = $entity_data_class::getList(array(
                                                  "select" => array("ID","UF_TOKEN","UF_REFRESH_TOKEN"),
                                                  "order" => array("ID" => "ASC"),
                                                  "filter" => array("UF_USER_ID"=>$user_id,"ID"=>$auth_id)  // Задаем параметры фильтра выборки
                                              ));

        if($arData = $rsData->Fetch()){
            return $arData;
        }else
            return false;
    }

}