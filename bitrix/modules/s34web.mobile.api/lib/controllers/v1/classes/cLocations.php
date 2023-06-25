<?php
namespace s34web\Mobile\Api\controllers\v1\classes;
/**
 * MainDEV: Alex Rilkov
 * Dev: Evgeniy Cimonov
 */

use Bitrix\Forum\Internals\Error\Error;
use \Bitrix\Main\Loader;
use \Bitrix\Iblock\PropertyEnumerationTable;
use \Dadata\DadataClient;

class cLocations
{
    const CACHE_ID = "locations";
    //const DADATA_TOKEN = "----------";
    const MIN_QUERY_LEN = 3;
    const DETECT_COORD_RADIUS = 100;
    const DETECT_COORD_VARIANTS_COUNT = 10;
    const isLocationRestricted = true;

    public static function getLocationsFixed()
    {

      $result = new ResultData();
      $action = function () {
        $result = [
          [
            "ID"=> "0000428505",
            "CITY"=> "Волжский"
          ],
          [
            "ID"=> "0000426112",
            "CITY"=> "Волгоград"
          ]
        ];
        return  ['result' => $result, 'error' => "", 'error_message' => ""];

      };
      //Кэширование статического списка отключено. При переработке хранения из базы, необходимо включить
      $data = self::cachedataD7('getLocationsFixed', $action, 'getLocationsFixed', 0);

      if (!$data['error'] && !$data['error_message']) {
        $result->setData($data['result']);
      } else {
        $result->setErrors($data['error'], $data['error_message']);
      }

      return $result;
    }

  /**
     * Метод для получения списка местоположений
     * Выгружаем список местоположений по запросу
     * @param $query
     * @return ResultData
     */
    public static function getLocations($query)
    {
        $result = new ResultData();
        if (empty($query)) {
            $result->setErrors(
                [
                    [cErrors::INCORRECT_REQUIRE_PARAMS,["PARAMS"=>"query"]]
                ]
            );
            $result->setStatusCode(400);
            return $result;
        }

        if (strlen($query)<self::MIN_QUERY_LEN)
            $result->setErrors(
                [
                    [cErrors::MINIMUM_LENGHT_PARAMS, ["MIN_LENGHT"=>self::MIN_QUERY_LEN." символа"]]
                ]
            );
        else {

            $action = function () use ($query) {
                return cLocations::getLocationByQuery($query);
            };
            $data = self::cachedataD7('getLocations', $action, 'getLocations' . md5($query), 604800);

            if (!$data['error'] && !$data['error_message']) {
                if(count($data['result'])==0)
                    $result->setStatusNotFoundError();
                $result->setData($data['result']);
            } else {
                $result->setErrors($data['error'], $data['error_message']);
            }
        }
        return $result;
    }

    /**
     * @param $name
     * @param $function
     * @param $cache_id
     * @param $cacheLifetime
     * @param int $iblock_id
     * @return string|array
     */
    private static function cachedataD7($name, $function, $cache_id, $cacheLifetime, $iblock_id = 0)
    {
        $cache_id .= "|lang=".cGeneral::getLang();
        return cGeneral::cacheDataIBlock(self::CACHE_ID,$name, $function, $cache_id, $cacheLifetime, $iblock_id,false);
    }

    public static function getLocationByCode($code, $lang="ru")
    {
        if(\Bitrix\Main\Loader::includeModule("sale"))
        {
            $res = \Bitrix\Sale\Location\LocationTable::getList(array(
                                                                'filter' => array('=NAME.LANGUAGE_ID' => $lang, 'TYPE_CODE' => array(/*"VILLAGE",*/ "CITY"), 'CODE' => $code),
                                                                'select' => array('CODE', 'NAME_RU' => 'NAME.NAME', 'TYPE_CODE' => 'TYPE.CODE'),
                                                                'limit' => 1,
                                                                'cache'=>['ttl'=>36000]
                                                            ));
            if ($item = $res->fetch())
            {
                $result = [
                    "ID" => $item["CODE"],
                    "CITY" => $item["NAME_"/*.$item["LANGUAGE_ID"]*/.strtoupper($lang)],
                    "FULL_NAME" => \Bitrix\Sale\Location\Admin\LocationHelper::getLocationStringByCode($item["CODE"])
                ];
                return $result;
            }

            //"VILLAGE", "CITY" -поселок или город
        }
        return false;
    }

    private static function getLocationByQuery($query, $lang="ru")
    {
        $result = [];
        $error = "";
        $error_message = "";

        if(\Bitrix\Main\Loader::includeModule("sale"))
        {
          $arFilter = array('=NAME.LANGUAGE_ID' => $lang, 'TYPE_CODE' => array(/*"VILLAGE",*/ "CITY"), 'NAME_'.strtoupper($lang) => $query . "%");
          if(self::isLocationRestricted)
            $arFilter["CODE"] = ["0000426112","0000428505"];

            $res = \Bitrix\Sale\Location\LocationTable::getList(array(
                'filter' => $arFilter,
                'select' => array('ID', 'CODE', 'NAME_'.strtoupper($lang) => 'NAME.NAME', 'TYPE_CODE' => 'TYPE.CODE', 'LANGUAGE_ID'=>'NAME.LANGUAGE_ID'),
                'limit' => 10
            ));
            while ($item = $res->fetch())
            {
                $result[] = [
                    "ID" => $item["CODE"],
                    "CITY" => $item["NAME_".strtoupper($item["LANGUAGE_ID"])],
                    //"REGION" => $item["NAME_"/*.$item["LANGUAGE_ID"]*/."ru"],
                    "FULL_NAME" => \Bitrix\Sale\Location\Admin\LocationHelper::getLocationStringByCode($item["CODE"])
                ];
                //echo Bitrix\Sale\Location\Admin\LocationHelper::getLocationPathDisplay($item["CODE"])."<br>";
            }
            //"VILLAGE", "CITY" -поселок или город
        }
        return ['result' => $result, 'error' => $error, 'error_message' => $error_message];
    }

    /**
     * https://dadata.ru/api/geolocate/
     *
     * @param float $lat
     * @param float $lon
     * @return mixed
     */
    public static function getLocationsByCord(float $lat,float $lon)
    {
        $result = new ResultData();
        require_once ($_SERVER["DOCUMENT_ROOT"]."/vendor/autoload.php");
        $data = [];
        try {
           $token_dadata = cGeneral::getDadataToken();
            $dadata = new DadataClient($token_dadata, null);
            $d = $dadata->geolocate("address",
              $lat,
              $lon,
              self::DETECT_COORD_RADIUS,
              self::DETECT_COORD_VARIANTS_COUNT
            );
            $items = [];
            if($d)
            {
                foreach ($d as $di)
                {
                  if($di["data"]["house"]) {
                    //$items[] = $di["value"];

                    $value = [$di["data"]["street_with_type"]];
                    if ($di["data"]["house"])
                      $value[] = (" " . $di["data"]["house_type"] . " " . $di["data"]["house"]);
                    $items[] = [
                      //"id"=> $di["data"]["kladr_id"]//"34000002000003100",
                      "address" => implode(",", $value),//$di["value"]
                      "city" => ($di["data"]["city_with_type"] ? $di["data"]["city_with_type"] . ", " : "")
                        . $di["data"]["region_with_type"]
                    ];
                  }
                }
            }
            $data = $items;
        }catch (\Exception $ex)
        {
            $data['error_message'] = $ex->getMessage();
        }
        if (!$data['error'] && !$data['error_message']) {
            $result->setData($data);
        } else {
            $result->setErrors($data['error'], $data['error_message']);
        }
        return $result;
    }

    /**
     * https://dadata.ru/api/suggest/address/
     * https://confluence.hflabs.ru/pages/viewpage.action?pageId=222888017
     * @param $query
     * @return mixed
     */
    public static function getLocationsByQuery($query)
    {
        $result = new ResultData();
        $data = [];

        if(strlen($query)<self::MIN_QUERY_LEN)
        {
            $result->setErrors(
                [
                    [cErrors::MINIMUM_LENGHT_PARAMS, ["MIN_LENGHT"=>self::MIN_QUERY_LEN." симв."]]
                ]
            );
        }else {
            require_once($_SERVER["DOCUMENT_ROOT"] . "/vendor/autoload.php");
            try {
                $token_dadata = cGeneral::getDadataToken();
                $dadata = new DadataClient($token_dadata, null);
                $arFilterLocations =  [
                ];
                if(self::isLocationRestricted)
                  $arFilterLocations = array_merge($arFilterLocations, [[ "city"=> "Волжский"], [ "city"=> "Волгоград"]]);
                else{
                  $arFilterLocations = array_merge($arFilterLocations,  [
                  "region" => "волгоградская",
                    "country_iso_code" => "RU"
                  ]);
                }

                $d = $dadata->suggest(
                    "address",
                    $query,
                    10,
                    [
                        //"locations_geo" => [ "lat" => 48.78583,  "lon" => 44.77973, "radius_meters" => 10000 ],
                        "locations" => $arFilterLocations,
                        "locations_boost"=> [
                            ["kladr_id"=> "3400000100000"],
                            ["kladr_id"=> "3400000200000"],
                        ],
                        "from_bound"=> [ "value"=> "region"],
                        "to_bound"=> [ "value"=> "house"],//до дома
                        "restrict_value" => true
                    ]
                );
                $items = [];
                if ($d) {
                    foreach ($d as $di) {
                        $value = [$di["data"]["street_with_type"]];
                        if($di["data"]["house"])
                            $value[] = (" ".$di["data"]["house_type"]." ".$di["data"]["house"]);
                        $items[] = [
                            //"id"=> $di["data"]["kladr_id"]//"34000002000003100",
                            "address" => implode(",", $value),//$di["value"]
                            "city" => ($di["data"]["city_with_type"] ? $di["data"]["city_with_type"] . ", " : "")
                                . $di["data"]["region_with_type"]
                        ];
                    }
                }
                $data = $items;
                unset($d);
            } catch (\Exception $ex) {
                $data['error_message'] = $ex->getMessage();
            }
        }
        if (!$data['error'] && !$data['error_message'])
        {
            $result->setData($data);
        } else {
            $result->setErrors($data['error'], $data['error_message']);
        }
        return $result;
    }

}