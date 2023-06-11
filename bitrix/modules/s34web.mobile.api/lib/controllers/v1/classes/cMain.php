<?php
namespace s34web\Mobile\Api\controllers\v1\classes;

/**
 * MainDEV: Alex Rilkov
 */

class cMain
{
  public const CACHE_ID = "content";

  /**
   * Метод для получения информации о компании
   *
   * @return ResultData
   */
  public static function getCompanyInfo()
  {
    return cMain::getStaticContent("company");
  }

  /**
  * Метод для получения информации оплате
  *
  * @return ResultData
  */
  public static function getPaymentInfo()
  {
     return cMain::getStaticContent("payment");
  }

   /**
   * Метод для получения информации о доставке
   *
   * @return ResultData
   */
  public static function getDeliveryInfo()
  {
    return cMain::getStaticContent("delivery");
  }

  private static function getStaticContent(string $elementCode): ResultData
  {
      $result = new ResultData();
      $staticContentIblockId = cGeneral::getIBlockIDByLang("staticContent");

      $action = function () use ($staticContentIblockId, $elementCode) {
          $error_message = "";
          $error = 0;
          $result = [];
          $convertDictionary = [
              'DETAIL_TEXT' => 'text'
          ];
          try {
              $arSelect = ['DETAIL_TEXT'];
              $arFilter = ["CODE" => $elementCode];
              $servicesElements = self::getElements($staticContentIblockId, $arSelect, $arFilter);
              if( $servicesElements)
              {
                $result = cGeneral::convertDataElement($servicesElements[0], $convertDictionary);
              }else{
                  $error = cErrors::NOT_EXIST_ID;
              }
          }catch (\Exception $ex)
          {
              $error = cErrors::NO_DATA;
          }
          return ['result' => $result, 'error' => $error, 'error_message' => $error_message];
      };

      $data = self::cachedataD7($elementCode, $action, 'get'.$elementCode, 604800, $staticContentIblockId);
      if (!$data['error'] && !$data['error_message']) {
          $result->setData($data['result']);
      } else {
          $result->setErrors($data['error'], $data['error_message']);
      }

      return $result;
  }

    /**
     * Метод для получения списка услуг
     *
     * @return ResultData
     */
  public static function getServiceList()
  {
      $result = new ResultData();
      $servicesIblockId = cGeneral::getIBlockIDByLang("services");

      $action = function () use ($servicesIblockId) {
          $error_message = "";
          $error = 0;
          $result = [];
          $convertDictionary = [
              'ID' => ['new_key' => 'id', 'data_type' => 'int'],
              'NAME' => ['new_key' => 'name', 'data_type' => 'stringNotNull'],
              'PREVIEW_TEXT' => ['new_key' => 'summary', 'data_type' => 'html'],
              'PREVIEW_PICTURE' => ['new_key' => 'picture_url', 'data_type' => 'image']
          ];

          $arSelect = ['ID', 'NAME', 'PREVIEW_TEXT', 'PREVIEW_PICTURE'];
          $arFilter = ['ACTIVE' => "Y", '=HIDE_IN_MOBILE.VALUE' => null];

          try {
              $servicesElements = self::getElements($servicesIblockId, $arSelect, $arFilter);

              $result = cGeneral::convertDataArray($servicesElements, $convertDictionary);
          }catch (\Exception $ex)
          {
              $error = cErrors::NO_DATA;
          }
          return ['result' => $result, 'error' => $error, 'error_message' => $error_message];
      };

      $data = self::cachedataD7('ServicesList', $action, 'getServicesList', 604800, $servicesIblockId);
      if (!$data['error'] && !$data['error_message']) {
          $result->setData($data['result']);
      } else {
          $result->setErrors($data['error'], $data['error_message']);
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
        return cGeneral::cacheDataIBlock(self::CACHE_ID,$name, $function, $cache_id, $cacheLifetime, $iblock_id);
    }

    private static function getElements(int $iblockId, array $arSelect, array $arFilter, array $arOrder=[])
    {
      try {
        $iBlock = \Bitrix\Iblock\Iblock::wakeUp($iblockId);
        $params = [];
        if($arSelect)
          $params['select'] = $arSelect;
        if($arFilter)
          $params['filter'] = $arFilter;
        if($arOrder)
          $params['order'] = $arOrder;

        return $iBlock->getEntityDataClass()::getList(
          $params
        )->fetchAll();
      }catch (\Exception $ex)
      {
        cGeneral::dump("Ошибка получения getElements iblockId=".$iblockId.", ".$ex->getMessage(),"errors_main");
        return false;
      }
    }

}