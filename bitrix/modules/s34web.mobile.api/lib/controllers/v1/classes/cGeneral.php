<?php
namespace s34web\Mobile\Api\controllers\v1\classes;
/**
 * User: Alex Rilkov 
 * Maker: Evgeniy Cimonov
 */

use Bitrix\Main\Application;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\Config\Option;
use Bitrix\Main\Data\Cache;
use Bitrix\Main\Diag\Debug;
use Bitrix\Main\GroupTable;
use Bitrix\Main\Loader;
use Bitrix\Highloadblock\HighloadBlockTable;
use Bitrix\Main\SystemException;
use Bitrix\Main\UserTable;
use Bitrix\Main\Web\Json;
use Bitrix\Sale\Delivery\Services\Table;
use Bitrix\Sale\Internals\PaySystemActionTable;
use CCurrencyLang;
use CFile;
use CMainPage;
use CSaleStatus;
use CUser;
use DateTime;
use Exception;
use Nowakowskir\JWT\Exceptions\TokenExpiredException;
use s34web\Mobile\Api\Request;
use s34web\Mobile\Api\Response;

class cGeneral
{
    const STOCKS_IBLOCK_ID = 19;
    const STOCKS_IBLOCK_ID_EN = 19;
    const CATALOG_IBLOCK_ID = 17;
    const PRODUCTS_IBLOCK_ID = 4;
    const BRANCHES_IBLOCK_ID = 5;
    const USERS_IBLOCK_ID = 1;
    const CARS_IBLOCK_ID = 2;
    const CATALOG_IBLOCK_ID_EN = 17;
    const OFFERS_IBLOCK_ID = 20;
    const OFFERS_IBLOCK_ID_EN = 20;
    const SERVICES_IBLOCK_ID = 14;
    const SERVICES_IBLOCK_ID_EN = 14;
    const CONTACTS_IBLOCK_ID = 17;
    const CONTACTS_IBLOCK_ID_EN = 17;
    const STATIC_CONTENT_IBLOCK_ID = 51;
    const STATIC_CONTENT_IBLOCK_ID_EN = 51;

    const MOBILE_USERS_GROUP_NAME = 'mobile_users';

    const IS_CACHE_ACTIVE = true;
    const IS_DATA_EMPTY_ERROR = true;//Отправлять пустой ответ
    const SKIP_METHOD_CHECK = true;
    const IS_NEED_CHECK_MOBILE_GROUP = false;
    const BIRTHDAY_UPDATE_IS_LOCKED = true;
    //const FAKE_USER_ID = 89;//Временный пользователь для проверки корзины.

    public static $IS_CACHE_ACTIVE = [
        cMain::CACHE_ID  => true,
        cCatalog::CACHE_ID  => true,
    ];

    public static $lang="ru";
    private static $langIblocksConstant = false;
    private static $langIblocksConstantDefault = [
        "ru"=>[
            "contacts"=>self::CONTACTS_IBLOCK_ID,
            "catalog"=>self::CATALOG_IBLOCK_ID,
            "offers"=>self::OFFERS_IBLOCK_ID,
            "stocks"=>self::STOCKS_IBLOCK_ID,
            "services"=>self::SERVICES_IBLOCK_ID,
            "staticContent"=>self::STATIC_CONTENT_IBLOCK_ID,
            "products" => self::PRODUCTS_IBLOCK_ID,
            "branches" => self::BRANCHES_IBLOCK_ID,
            "users" => self::USERS_IBLOCK_ID,
            "cars" => self::CARS_IBLOCK_ID,
        ],
        "en"=>[
            "contacts"=>self::CONTACTS_IBLOCK_ID_EN,
            "catalog"=>self::CATALOG_IBLOCK_ID_EN,
            "offers"=>self::OFFERS_IBLOCK_ID_EN,
            "stocks"=>self::STOCKS_IBLOCK_ID_EN,
            "services"=>self::SERVICES_IBLOCK_ID_EN,
            "staticContent"=>self::STATIC_CONTENT_IBLOCK_ID_EN,
        ]
    ];
    private static $testPhone = ["+79001234567"];
    private static $testSmsCode = "12345";

    public static function getTestPhone(): array
    {
      return self::$testPhone;
    }

    public static function getTestSmsCode(){
      return self::$testSmsCode;
    }

    public static function isSendSms()
    {
      return Option::get("s34web.mobile.api","IS_SEND_SMS","N")=="Y";
    }

    //Получение id пользователя для привязки заказов
    public static function getFakeUserID()
    {
      $fake_user_id = Option::get("s34web.mobile.api","FAKE_USER_ID","0");
      if($fake_user_id<=0)
      {
        $users = UserTable::getList(array("filter"=>["EMAIL"=>"fake@site.ru"], "select" => array("ID")));
        if (!$users->fetch())
        {
          //Добавление фейкового пользователя
          $user = new CUser;
          $arFields = array(
            "NAME" => "Временный",
            "LOGIN" => "fake_user_for_mobile",
            "EMAIL" => "fake@site.ru",
            "PHONE_NUMBER" => "+79003456789",
            "LID" => "ru",
            "ACTIVE" => "Y",
            "PASSWORD" => "VDE125753dfw",
            "CONFIRM_PASSWORD" => "VDE125753dfw",
            //      "GROUP_ID" => array(10, 11)
          );
          $fake_user_id = (int)$user->Add($arFields);
          if($fake_user_id)
            Option::set("s34web.mobile.api","FAKE_USER_ID",$fake_user_id);
        }
      }
      return $fake_user_id;

    }

    public static function setLang($lang="ru")
    {
          self::$lang = in_array($lang,["ru","en"])?$lang:"ru";
    }

    public static function getLang($lang=false)
    {
          if(!$lang)
          {
              $lang = self::$lang;
          }
          return $lang;
    }

    public static function isInt($text)
    {
        return (ctype_digit($text) && intval($text)==$text);
    }

    public static function getIBlockIDByLangOLD($code, $lang=false)
    {
        $lang = self::getLang($lang);
        return self::$langIblocksConstantDefault[$lang][$code];
    }

    public static function getIBlockIDByLang($code, $lang=false)
    {
      $lang = self::getLang($lang);

      if(!self::$langIblocksConstant) {

        $constants = Option::get("s34web.mobile.api", "LANG_IBLOCKS_CONSTANT", false);

        if ($constants) {
          $data = unserialize($constants);
          self::$langIblocksConstant = $data;
        } else {
          $data = serialize(self::$langIblocksConstantDefault);
          self::$langIblocksConstant = self::$langIblocksConstantDefault;
          Option::set("s34web.mobile.api", "LANG_IBLOCKS_CONSTANT", $data);
        }
        return self::$langIblocksConstant[$lang][$code];
      }else{
        return self::$langIblocksConstant[$lang][$code];
      }
    }

    public static function prepare_slashes($text)
    {
        return $text;
    }

    /**
     * @param $type
     * @param $name
     * @param $function
     * @param $cache_id
     * @param $cacheLifetime
     * @param int $iblock_id
     * @param bool $active_chache
     * @return string|array
     */
    public static function cacheDataIBlock($type, $name, $function, $cache_id, $cacheLifetime, $iblock_id = 0, $active_chache=true){
        if(is_callable($function)){
            if(empty($type))
                $type = "content";
            if(self::$IS_CACHE_ACTIVE[$type] && $active_chache ) {
                $result = null;
                $obCache = Cache::createInstance();
                $cachePath = '/apicache/'.$type.'/get' . $name;
                if($cacheLifetime<0) {
                    $obCache->cleanDir($cachePath);
                    $cacheLifetime = abs($cacheLifetime);
                }
                //При нулевом кэшировании выдаём сразу результат
                if($cacheLifetime==0)
                {
                  $result = $function();
                }else {
                  if ($obCache->initCache($cacheLifetime, $cache_id, $cachePath)) {
                    $result = $obCache->GetVars();
                  } elseif ($obCache->startDataCache()) {
                    $result = $function();
                    if ($iblock_id !== 0) {
                      $GLOBALS['CACHE_MANAGER']->StartTagCache($cachePath);
                      if (!is_array($iblock_id)) {
                        $iblock_id = (array)$iblock_id;
                      }
                      foreach ($iblock_id as $id) {
                        $GLOBALS['CACHE_MANAGER']->RegisterTag("iblock_id_" . $id);
                      }
                      $GLOBALS['CACHE_MANAGER']->EndTagCache();
                    }
                    if (!empty($result)) {
                      $obCache->endDataCache($result);
                    } else {
                      $obCache->abortDataCache();
                    }
                  }
                }
            }else{
                $result = $function();
            }
            return $result;
        }else{
            return cErrors::getText(cErrors::NO_FUNCTION);
        }
    }

    public static function get_entity_data_class($hlblock_name)
    {
        if (Loader::includeModule('highloadblock'))
        {
            if(is_numeric($hlblock_name)) {
                $hlblock_id = intval($hlblock_name);
                if ($hlblock_id>0 && $hlblock = HighloadBlockTable::getById($hlblock_id)->fetch()) {
                    $entity = HighloadBlockTable::compileEntity($hlblock);
                    $entity_data_class = $entity->getDataClass();
                    return $entity_data_class;
                }
            }else{
                if ($hlblock = HighloadBlockTable::getList(
                    array("filter" => array(
                        'TABLE_NAME' => $hlblock_name
                    )))->fetch())
                {
                    $entity = HighloadBlockTable::compileEntity($hlblock);
                    $entity_data_class = $entity->getDataClass();
                    return $entity_data_class;
                }
            }
        }
        return false;
    }

    public static function getYoutubeFullPath($id=''){
        return  "https://www.youtube.com/embed/".$id;
    }

    public static function getCurrentServer()
    {
      static $server_name = "";
      if($server_name=="")
      {
        $server_name = Option::get("s34web.mobile.api", 'API_SERVER_NAME',"site.ru");
        //$_SERVER['SERVER_NAME']
      }
      return $server_name;
    }

    public static function getSiteServer()
    {
      $s =self::getCurrentServer();
      $start = stripos($s,".");
      if($start != false)
      {
        $s = substr($s, $start+1);
      }
      return $s;
    }

    public static function getFullPath($string='')
    {
      if(!empty($string)) {
        $protocol = "https://";
        //return $protocol . self::getSiteServer() . $string; //Удаление поддомена
        return $protocol . self::getCurrentServer() . $string;
      }else
        return "";
    }

    public static function getFullPathStandart()
    {
        //return self::getFullPath("/local/templates/aquarelle/images/logo.svg");
        return self::getFullPath("/local/templates/aspro_next_34web/custom-images/logo.png");
        //return self::getFullPath("/local/templates/aquarelle/images/logo.png");
    }

    public static function getMobileUserGroupId()
    {
        $MobileUserGroupName = Option::get("s34web.mobile.api","MOBILE_USER_GROUP_NAME","");

        if(empty($MobileUserGroupName))
          Option::set("s34web.mobile.api","MOBILE_USER_GROUP_NAME", cGeneral::MOBILE_USERS_GROUP_NAME);
        else
          $MobileUserGroupName = cGeneral::MOBILE_USERS_GROUP_NAME;

        $MobileUserGroupID = 0;
        //проверить наличие созданной группы, при отсутствии или добавление новой привязки
        try {
          $res = GroupTable::getList(array('filter' => array('=STRING_ID' => $MobileUserGroupName)));
          if ($row = $res->fetch()) {
            if ($row['ACTIVE'] == 'Y')
              $MobileUserGroupID =  $row["ID"];
          } else {
            $id = GroupTable::add(
              [
                "fields" => [
                  'NAME' => 'Зарегистрированные пользователи для мобильного приложения',
                  'DESCRIPTION' => 'Пользователи зарегистрированные через мобильное приложение',
                  'STRING_ID' => $MobileUserGroupName,
                  'ACTIVE' => 'Y'
                ]
              ]);
            $MobileUserGroupID = $id["ID"];
          }
        } catch (Exception $e) {
          //print_r($e->getMessage());
          cGeneral::dump("Ошибка при получение группы пользователей: ".$e->getMessage());
        }
        return $MobileUserGroupID;
    }

    public static function accessByTest()
    {
        return $_SERVER["SERVER_NAME"]=="api.dev.site.ru" || $_SERVER["SERVER_NAME"]=="dev.site.ru";
    }

    public static function accessByTestResult(&$arResult)
    {
        if(self::accessByTest())
            return true;
        $arTmp = new ResultData();
        $arTmp->setErrors(cErrors::NO_ACCESSBYSITE);
        $arResult = $arTmp->getResult();
        return false;
    }

    public static function getRequestPersonal($model=[])
    {
        return  self::getRequest($model,true);
    }

    /**
     * @param array $model
     * @param bool $is_closed
     * @return array
     * @ignore
     * Get current request
     */
    public static function getRequest($model=[],$is_closed=false)
    {
        $ar = Request::get();
        /*Общие параметры*/
        $lang = $ar["parameters"]["lang"];
        if (!in_array($lang, ["ru"])) {
            $lang = "ru";
        }
        cGeneral::dump(["getRequest",$ar],"general");
        cGeneral::setLang($lang);
        if($ar["AUTHORIZATION_TOKEN"])
          $ar["parameters"]["token"] = $ar["AUTHORIZATION_TOKEN"];
        $token = $ar["parameters"]["token"];
        if($ar["REFRESH_TOKEN"])
          $ar["parameters"]["refresh_token"]  = $ar["REFRESH_TOKEN"];
        //$refresh_token=$ar["parameters"]["refresh_token"];
        //Проверка авторизации по токену, если запрос без авторизации не работает
        if ($is_closed) {

          if($ar["parameters"]["d"]=="Y")
            print_r(
              [
                "X-AUTH-TOKEN"=>$_SERVER["HTTP_X_AUTH_TOKEN"],
                "X-MOBILE-DEVICE-ID"=>$_SERVER["HTTP_X_MOBILE_DEVICE_ID"],
                "X-AUTH-REFRESH-TOKEN"=>$_SERVER["HTTP_X_AUTH_REFRESH_TOKEN"],
                $ar
              ]);

            try {
                //Если токен присутствует в запросе
                //Проверка валидности токена и времени его окончания, если токен закончился выдаём ошибку 401

                if(!$token)
                {
                  $result = new ResultData();
                  $result->setErrors(cErrors::NO_TOKEN);//Не указан токен для закрытого метода
                  $result->setStatusAuthError();
                  self::sendResponse($result);
                  $ar = false;
                }else
                if ($payload = cAuth::validateToken($token))
                {
                    $user_id = intval($payload["user_id"]);
                    $auth_id = intval($payload["auth_id"]);
                    $device_token = $payload["device_token"];
                    $ar["parameters"]["device_token"] = $device_token;
                    $ar["user_id"] = $user_id;

                    //Проверка существования пользователя в базе и получение его данных
                    $user = self::getUserByID($user_id);
                    $user_auth = cAuthTable::getAuthByID($user_id, $auth_id);

                    //Найден пользователь в базе по id и проверить корректность токенов для устройства
                    if ($user == false || $user_auth["UF_TOKEN"] != $token) {
                        $result = new ResultData();
                        $result->setErrors(cErrors::TOKEN_FORMAT_ERROR);
                        $result->setStatusAuthError();
                        self::sendResponse($result);
                        $ar = false;
                    }
                }else{
                    $result = new ResultData();
                    $result->setErrors(cErrors::TOKEN_FORMAT_ERROR);//"Ошибка token payload"

                    $result->setStatusAuthError();
                    self::sendResponse($result);
                    $ar = false;
                }
            } catch (TokenExpiredException $e)
            {
                //Ждём повторный запрос на продление срока доступа при наличии refresh_token

                $result = new ResultData();
                $result->setErrors(-1,"Токен закончился, нужно выполнить отдельно метод refresh для продления или выполнить метод register снова.");

                //$result->setParam("error", Errors::NO_USER_WITH_THIS_TOKEN);
                $result->setStatusAuthTokenExpectedError();//Ошибка 401 для продления токена
                self::sendResponse($result);
                $ar = false;

            } catch (Exception $e) {
                $result = new ResultData();
                $result->setErrors(-1,$e->getMessage());
                $result->setStatusAuthError();
                self::sendResponse($result);
                $ar = false;
            }
        }else{
          if($ar["DEVICE_ID"])
            $ar["parameters"]["device_token"] = ($ar["DEVICE_ID"]);
        }
        cGeneral::dump(["getRequest",$ar],"general2");
        $errors = [];
        //Проверка модели параметров
        if(count($model)>0)
        {
            foreach ($model as $code=>$type)
            {
                if(intval($code)>0 || $code===0)
                {
                  $code = $type;
                  $type = "";
                }
                $is_require = true;
                if(is_array($type))
                {
                  $arType = $type;
                  $type = $arType["type"];
                  if(array_key_exists("require",$arType))
                  {
                    $is_require = $arType["require"] == "true";
                  }
                }
                if(array_key_exists($code, $ar["parameters"]))
                {
                  $val = cGeneral::filterByType($ar["parameters"][$code], $type);
                  if($val!==false)
                     $ar["parameters"][$code] = $val;
                  elseif($is_require)
                     $errors[] = $code;
                }elseif($is_require){
                    $errors[] = $code;
                }
            }
        }

        if($errors)
        {
            $result = new ResultData();

            $result->setErrors(
                [
                    [cErrors::INCORRECT_REQUIRE_PARAMS, ["PARAMS" => implode(",",$errors)]]
                ]
            );
            $result->setStatusBadRequestError();
            self::sendResponse($result);
            $ar = false;
        }

        return $ar;
    }

    /**
     * @param string $token
     * @param string $refresh_token
     * @param int $user_id
     * @return array|false
     * @throws SystemException
     */
    public static function getUserByID(int $user_id)
    {
        if($user_id>0) {
            $arFilter = ["ACTIVE"=>"Y"];
            $arFilter["ID"] = $user_id;

            if(cGeneral::IS_NEED_CHECK_MOBILE_GROUP) {
                $groupId = cGeneral::getMobileUserGroupId();
                if ($groupId > 0) {
                    $arFilter["Bitrix\Main\UserGroupTable:USER.GROUP_ID"] = $groupId;
                }
            }
            try {
                $arUser = UserTable::getList([
                                              "select" => [
                                                  "ID",
                                                  "NAME",
                                              ],
                                              "filter" => $arFilter,
                                              "order" => [
                                                  'ID' => 'ASC'
                                              ],
                                              "data_doubling" => false
                                          ])->fetch();
                if (!empty($arUser))
                {
                    return $arUser;
                }
            } catch (ArgumentException $e) {
                return false;
            }
        }
        return false;
    }


    // ADDITIONAL METHOD

    /**
     * @param ResultData $arResult
     * @param array $arParams
     * @ignore
     */
    public static function sendResponse(ResultData $arResult, $arParams=[])
    {
        $data = $arResult->getResult();
        /*if($arParams["is_refresh"])
        {
            $data["token"] = $arParams["token"];
            $data["refresh_token"] = $arParams["refresh_token"];
        }*/

      if(!$arResult->isSuccess() || $arResult->getStatusCode()!=200)
      {
          /*if(is_array($data["errors"]))
              $data = $data["errors"]["error_message"];*/

        switch ($arResult->getStatusCode())
          {
              case 400:
                  Response::BadRequest($data);
                  break;
              case 401:
                  Response::UnAuthRequest($data);
                  break;
              case 404:
                  Response::NotFoundRequest($data);
              break;
              default:
                  Response::SendResult($data, $arResult->getStatusCode());
              break;
          }

      } else {
          Response::ShowResult($data);
      }
    }

    /**
    * @param $arResult
    * @param string $method
    * @return bool
    * @ignore
    */
    public static function checkMethod($arResult, string $method)
    {

        if((cGeneral::SKIP_METHOD_CHECK && $arResult["parameters"]["_method"]==$method)
            || (!isset($arResult["parameters"]["_method"]) && cGeneral::SKIP_METHOD_CHECK && $arResult["request_method"]==$method)
            || (!cGeneral::SKIP_METHOD_CHECK && $arResult["request_method"]==$method)){
            return true;
        }
        return false;
    }

    /**
     * @param $arResult
     * @param string $method
     * @return bool
     * @ignore
     */
    public static function checkMethodRequire($arResult,$method="POST")
    {
        if(self::checkMethod($arResult,$method) || cGeneral::SKIP_METHOD_CHECK) {
            return true;
        }else {
            $arTmp = new ResultData();
            $arTmp->setErrors(cErrors::INCORRECT_METHOD);
            Response::ShowResult($arTmp->getResult());
        }
        return false;
    }

    /** @param string $value
     * @param string $type
     * @return string
     * @ignore
     *
     */
    public static function filterByType($value, $type='default')
    {
        switch ($type)
        {

            case "string_en":
                if(!preg_match('/[^a-zA-ZЁё0-9\s]/u',$value))
                {
                    $value = false;
                }else
                    $value = strtolower($value);
                break;
            case "string_ru":
                if(!preg_match('/[^а-яА-ЯЁё0-9\s]/u',$value))
                {
                    $value = false;
                }else
                    $value = strtolower($value);
                break;

            case "string_all_filter":  //Фильтр лишних символов
                $value = preg_replace('/[^а-яА-Я0-9A-Za-zЁё\s]/u','', $value);
                $value = strtolower($value);
                break;
            case "string_all":
                if(!preg_match('/[^а-яА-Я0-9A-Za-zЁё\s]/u',$value))
                {
                    $value = false;
                }else
                    $value = strtolower($value);
                break;
            case "fio":

                if(preg_match('/[^а-яА-Я0-9A-Za-zЁё\s]/gu',$value))
                {
                  $value = false;
                }
            break;
            case "phone":
                if(preg_match('/[^0-9\s\-+(]/u',$value))
                {
                  $value = false;
                }else{
                  $value = str_replace(" ", "", $value);
                }
            break;
            case "phone_simle":
            if(preg_match('/[^0-9]/u',$value))
            {
              $value = false;
            }else{
              $value = str_replace(" ", "", $value);
            }
            break;
            case "address":
                if(empty($value))
                  $value = false;
            break;
            case "location_code":
                if(preg_match('/[^0-9]/u',$value))
                {
                  $value = false;
                }
                break;
            case "int":
                if(intval($value)==$value)
                {
                    $value = intval($value);
                }else
                    $value = false;
                break;
            case "double":
                if(floatval($value)==$value)
                {
                    $value = floatval($value);
                }else
                    $value = false;
                break;
            default:
              break;
                /*if(!preg_match('/./u',$value))
                {
                    $value = false;
                }*/
        }

        return $value;
    }

    /**
     * форматирование [+]79000123456 в +7 (961) 012-34-56
     *
     * @param string $ph
     * @return string
     */
    public static function formatPhone(string $ph)
    {
        if (substr($ph, 0, 1) == "+") {
            $ph = substr($ph, 1);
        }
        return "+" . $ph[0] . ' (' . $ph[1] . $ph[2] . $ph[3] . ') ' . $ph[4] . $ph[5] .
            $ph[6] . '-' . $ph[7] . $ph[8] . '-' . $ph[9] . $ph[10];
    }

    public static function getDayOfWeek($time="")
    {
      $day = $time? date("w",$time):date("w");
      if((int)$day===0)
        $day_from_mon = 6;
      else
        $day_from_mon = $day-1;

      return $day_from_mon;
    }

  public static function getTestSMSAuthMode()
  {
    return Option::get("s34web.mobile.api","IS_SEND_SMS","N")=="Y";
  }

  /*
   * Закрытый токен для симметричного шифрования статусов оплаты
   * */
  public static function getSberToken()
  {
    return Option::get("s34web.mobile.api","PAYMENTS_SBER_TOKEN","");
  }

  /**
   * @return bool
   */
  public static function isNameInOne(): bool
  {
    $return = false;
    try {
      // include CMainPage
      require_once($_SERVER['DOCUMENT_ROOT'] . "/bitrix/modules/main/include/mainpage.php");
      // get site_id by host
      $CMainPage = new CMainPage();
      $siteID = $CMainPage->GetSiteByHost();
      if (!$siteID)
        $siteID = "s1";

      if($t = Option::get("aspro.next", 'PERSONAL_ONEFIO', '(-)', $siteID)!="(-)")
        $return =  $t=="Y";
      else
        if($t = Option::get("aspro.maximum", 'PERSONAL_ONEFIO', '(-)', $siteID)!="(-)")
          $return =  $t=="Y";

    }catch (Exception $ex)
    {
      $return = false;
    } finally
    {
      return $return;
    }

  }

    public static function isAddMainPictureToSlides()
    {
        static $isAddMainPictureToSlides = null;
        if($isAddMainPictureToSlides == null)
            $isAddMainPictureToSlides = Option::get("s34web.mobile.api", 'IS_ADD_MAIN_PICTURE_TO_SLIDES', "N")=="Y";
        return $isAddMainPictureToSlides;
    }

  public static function getDadataToken()
  {
    static $DADATA_TOKEN = null;
    if($DADATA_TOKEN == null)
      $DADATA_TOKEN = Option::get("s34web.mobile.api", 'DADATA_TOKEN', "");
    return $DADATA_TOKEN;
  }

  private static function validateRefreshToken(string $token,string $refresh_token)
    {
        if(!empty($refresh_token))
            return strlen($token)>6 && strlen($refresh_token)>6 && substr($refresh_token,-6)==substr($token,-6);
        return false;
    }

    public static function convertDataArray(array $elements, array $convertDictionary)
    {
        $result = [];
        foreach ($elements as $arElement) {
            $result[] = self::convertDataElement($arElement, $convertDictionary);
        }
        return $result;
    }

    public static function validateDate(string $date, $format = 'Y-m-d H:i:s')
    {
        $d = DateTime::createFromFormat($format, $date);
        return $d && $d->format($format) == $date;
    }

    public static function convertDataElement(array $arElement, array $convertDictionary)
    {
        $result = [];
        foreach ($convertDictionary as $oldKey => $convertData)
        {
            if(is_array($convertData))
            {
                if($convertData["new_key"])
                    $newKey = $convertData["new_key"];
                else
                    $newKey = $oldKey;

                if($convertData["data_type"])
                    $dataType = $convertData["data_type"];
                else
                    $dataType = "string";
            }
            else
            {
                if($convertData)
                    $newKey = $convertData;
                else
                    $newKey = $oldKey;

                $dataType = "string";
            }

            switch ($dataType)
            {
                case "stringNotNull":
                  $result[$newKey] = $arElement[$oldKey]?:"";
                break;
                case "int":
                    if(ctype_digit($arElement[$oldKey]))
                        $result[$newKey] = intval($arElement[$oldKey]);
                    else
                        $result[$newKey] = $arElement[$oldKey];
                    break;
                case "float":
                    if(is_numeric($arElement[$oldKey]))
                        $result[$newKey] = floatval($arElement[$oldKey]);
                    else
                        $result[$newKey] = $arElement[$oldKey];
                    break;
                case "bool":
                    $value =  trim(mb_strtolower($arElement[$oldKey]));
                    $result[$newKey] = $value == "y" || $value == "yes" || $value == "1";
                    break;
                case "image":
                    $result[$newKey] = $arElement[$oldKey] > 0 ? cGeneral::getFullPath(
                        CFile::GetFileArray($arElement[$oldKey])["SRC"]
                    ) : null;
                    break;
                case "date_utc0":
                    $result[$newKey] =  $arElement[$oldKey] ? (int)gmstrftime("%s", $arElement[$oldKey]->getTimestamp()):null;
                    break;
                case "date_ymd":
                    $result[$newKey] =  $arElement[$oldKey] ? $arElement[$oldKey]->format("Ymd"):null;
                    break;
                case "date":
                    $result[$newKey] =  $arElement[$oldKey]?$arElement[$oldKey]->getTimestamp():null;
                    break;
                case "html":
                    //$result[$newKey] = str_replace('="', '=\"',$arElement[$oldKey]);
                    $result[$newKey] = str_replace("src=\"", 'src=\"' . cGeneral::getFullPath('/'), $arElement[$oldKey]);
                    $result[$newKey] = str_replace('src=\'/', 'src=\'' . cGeneral::getFullPath('/'), $result[$newKey]);
                    $result[$newKey] = str_replace('href=\'/', 'href=\'' . cGeneral::getFullPath('/'), $result[$newKey]);
                    $result[$newKey] = str_replace('href=\"/', 'href=\"' . cGeneral::getFullPath('/'), $result[$newKey]);
                    break;
                case "htmlNoImg":
                    //$result[$newKey] = str_replace('="', '=\"',  $arElement[$oldKey]);
                    $result[$newKey] =  preg_replace("/<img.+?>/","",$arElement[$oldKey]);
                    $result[$newKey] = str_replace('href=\'/', 'href=\'' . cGeneral::getFullPath('/'), $result[$newKey]);
                    $result[$newKey] = str_replace('href="/', 'href=\"' . cGeneral::getFullPath('/'), $result[$newKey]);
                    break;
                case "orderStatus":
                    $result[$newKey]["id"] = $arElement[$oldKey];
                    $result[$newKey]["name"] = CSaleStatus::GetByID($arElement[$oldKey])["NAME"];
                    break;
                case "orderPaySystem":
                    $result[$newKey]["id"] = intval($arElement[$oldKey]);
                    $result[$newKey]["name"] = PaySystemActionTable::getById($arElement[$oldKey])->fetch()["NAME"];
                    break;
                case "orderDelivery":
                    $result[$newKey]["id"] = intval($arElement[$oldKey]);
                    $result[$newKey]["name"] = Table::getById($arElement[$oldKey])->fetch()["NAME"];
                    break;
                default:
                    $result[$newKey] = $arElement[$oldKey];
                break;
            }
        }
        return $result;
    }

    public static function priceFormat(float $price)
    {
      return CCurrencyLang::CurrencyFormat($price,"RUB", false);
    }

    public static function priceFormatNumber(float $price)
    {
      /*if((((int)$price)-$price)!=0)
        return number_format($price,2,".","");
      else
        return (int)$price;*/
      return $price;
    }

    public static function dump($result, $file_suff="")
    {
//        Debug::writeToFile($result, "", "/logs/api/".$file_suff.".log");
    }

    public static function decodeName($name)
    {
        return html_entity_decode($name);
    }

    public static function getCacheDirectoryPath($type = 'catalogNew')
    {
        static $directoryFull = '';

        if($directoryFull === '') {
            $application = Application::getInstance();
            $context = $application->getContext();
            $server = $context->getServer();
            $documentRoot = $server->getDocumentRoot();
            $directory = '/bitrix/cache/apicache';

            $directoryFull = $documentRoot . $directory . '/' . $type;
        }
        return $directoryFull;
    }

    public static function getFiles(array $excluded = ['..', '.'])
    {
        $files = [];
        $directoryFull = self::getCacheDirectoryPath();
        if(file_exists($directoryFull)){
            $files = array_diff(scandir($directoryFull), $excluded);
        }
        return $files;
    }

    public static function setJson($data, $dir, $fileName, $putFlags = LOCK_EX)
    {
        if(!file_exists($dir)){
            mkdir($dir);
        }
        $string = Json::encode(
            $data,
            JSON_HEX_TAG|JSON_HEX_AMP|JSON_HEX_APOS|JSON_HEX_QUOT|JSON_UNESCAPED_UNICODE
        );
        return file_put_contents("$dir/$fileName", $string, $putFlags);
    }

    public static function setIndexJson($data, $dir, $fileName)
    {
        if(!file_exists($dir)){
            mkdir($dir);
        }
        $handle = @fopen("$dir/$fileName", 'r+');
        // create the file if needed
        if ($handle === false){
            $handle = fopen("$dir/$fileName", 'w+');
        }
        if ($handle) {
            // seek to the end
            fseek($handle, 0, SEEK_END);

            // are we at the end of is the file empty
            if (ftell($handle) > 0) {
                // move back a byte
                fseek($handle, -1, SEEK_END);

                // add the trailing comma
                fwrite($handle, ',', 1);

                // add the new json string
                fwrite(
                    $handle,
                    Json::encode(
                        $data,
                        JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE
                    ) . ']'
                );
            } else {
                // write the first event inside an array
                fwrite(
                    $handle,
                    Json::encode(
                        [$data],
                        JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE
                    )
                );
            }

            // close the handle on the file
            fclose($handle);
        }
    }

    public static function getJson($filepath)
    {
        $string = file_get_contents($filepath);
        return Json::decode($string);
    }

    public static function setSerialize($data, $dir, $fileName)
    {
        if(!file_exists($dir)){
            mkdir($dir);
        }
        $string = serialize($data);
        return file_put_contents("$dir/$fileName", $string, LOCK_EX);
    }

    public static function getSerialize($filepath)
    {
        $string = file_get_contents($filepath);
        return unserialize($string);
    }

    public static function getCachedData()
    {
        $directoryFull = self::getCacheDirectoryPath();
        return self::getJson($directoryFull . '/index.json');
    }

    public static function clearDatum(
        $datum,
        $clearProductField = [
            'active',
            'available',
            'xml_id',
            'timestamp_x'
        ],
        $clearOfferField = [
            'xml_id',
        ]
    )
    {
        $result = $datum;
        foreach ($clearProductField as $field) {
            if(isset($result[$field])){
                unset($result[$field]);
            }
        }
        unset($field);

        if(!empty($result['offers']) && is_array($result['offers'])){
            foreach ($result['offers'] as $offerKey => $offer) {
                foreach ($clearOfferField as $field) {
                    if(isset($offer[$field])){
                        unset($result['offers'][$offerKey][$field]);
                    }
                }
            }
        }

        return $result;
    }
}