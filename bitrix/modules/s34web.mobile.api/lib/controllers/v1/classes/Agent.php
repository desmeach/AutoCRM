<?php
/**
 * Created: 03.06.2021, 10:43
 * Author : Evgeniy Cimonov <cimonovevgeniy@34web.ru>
 * Company: 34web Studio
 */

namespace s34web\Mobile\Api\controllers\v1\classes;


use Bitrix\Bizproc\BaseType\Date;
use Bitrix\Main\Application;
use Bitrix\Main\Type\DateTime;

class Agent
{
    public static $errors = [];

    public static $limit = 100;//В приложении
    public static $module_id = "s34web.mobile.api";

    public static function updateAgentStatus($status)
    {
      \Bitrix\Main\Config\Option::set(self::$module_id,"AGENT_CACHE_CATALOG_STATUS",$status);
      \Bitrix\Main\Config\Option::set(self::$module_id,"AGENT_CACHE_CATALOG_STATUS_DATE",(new DateTime())->format("H:i:s d.m.Y"));
    }

    public static function cacheCatalog($error=false)
    {
      //При ошибке отправить уведомление администратору мобильного приложения.
      try {
        //self::updateAgentStatus("start");
        $files = cGeneral::getFiles();
        $count = self::$limit;

        if (empty($files)) {
          $offset = 0;
          self::updateAgentStatus("start generate new cache");
          while ($count == self::$limit) {
            $indexData = cCatalog::cacheBlock(self::$limit, $offset);
            $count = $indexData['count'];
            cGeneral::setIndexJson($indexData, cGeneral::getCacheDirectoryPath(), 'index.json');
            $offset += $count;
            //cGeneral::dump([$count, $offset], '_cache_catalog_1');
          }
        } else {
          $needUpdate = false;
          self::updateAgentStatus("start check update cache");
          $realData = cCatalog::getCatalogData();

          \Bitrix\Main\Config\Option::set(self::$module_id,"AGENT_CACHE_TEST",cGeneral::getCacheDirectoryPath() . '/index.json');

          $realCount = count($realData);
          $realXmlID = $realData['products'];
          $realOffersXmlID = $realData['offers'];
          self::updateAgentStatus("start check update cache (step 2)");
          $cachedData = cGeneral::getCachedData();

          $cachedLastUpdate = filemtime(cGeneral::getCacheDirectoryPath() . '/index.json');
          $cachedCount = 0;
          $cachedXmlID = [];
          $cachedOffersXmlID = [];
          if (!empty($cachedData) && is_array($cachedData)) {
            foreach ($cachedData as $block) {
              $cachedXmlID = array_merge($cachedXmlID, $block['products']);
              $cachedOffersXmlID = array_merge($cachedOffersXmlID, $block['offers']);
              $cachedCount += (int)$block['count'] ?? 0;
            }
          }

          $deletedXmlID = array_values(array_diff($cachedXmlID, $realXmlID));
          $addedXmlID = array_values(array_diff($realXmlID, $cachedXmlID));

          $deletedOffersXmlID = array_values(array_diff($cachedOffersXmlID, $realOffersXmlID));
          $addedOffersXmlID = array_values(array_diff($realOffersXmlID, $cachedOffersXmlID));

          self::updateAgentStatus("start check update cache (step 3)");
          $updatedXmlID = cCatalog::checkLastUpdate($cachedLastUpdate);

          //cGeneral::dump([$addedXmlID, $updatedXmlID], '_cache_catalog_1');

//            if (!empty($deletedOffersXmlID)) {
//                $productsXmlID = [];
//                foreach ($deletedOffersXmlID as $offerXmlID) {
//                    if (isset($realData['links'][$offerXmlID])) {
//                        $productsXmlID[] = $realData['links'][$offerXmlID];
//                    }
//                }
//                if(!empty($productsXmlID)){
//                    $productsXmlID = array_unique($productsXmlID);
//                    $tempXmlID = empty($deletedXmlID)
//                        ? $productsXmlID
//                        : array_diff($deletedXmlID, $productsXmlID);
//                    if(!empty($tempXmlID)){
//                        $updatedXmlID = array_merge($updatedXmlID, $tempXmlID);
//                    }
//                }
//            }

          if (!empty($addedOffersXmlID)) {
            $productsXmlID = [];
            foreach ($addedOffersXmlID as $offerXmlID) {
              if (isset($realData['links'][$offerXmlID])) {
                $productsXmlID[] = $realData['links'][$offerXmlID];
              }
            }
            if (!empty($productsXmlID)) {
              $productsXmlID = array_unique($productsXmlID);
              $tempXmlID = empty($addedXmlID)
                ? $productsXmlID
                : array_diff($addedXmlID, $productsXmlID);
              if (!empty($tempXmlID)) {
                $updatedXmlID = array_merge($updatedXmlID, $tempXmlID);
              }
            }
          }
          self::updateAgentStatus("start check update cache (step 5)");
          if (!empty($updatedXmlID)) {
            $updatedXmlID = empty($addedXmlID)
              ? $updatedXmlID
              : array_values(array_diff($updatedXmlID, $addedXmlID));
            self::updateCachedData($updatedXmlID, $cachedData);
            $needUpdate = true;
          }

          self::updateAgentStatus("start check update cache (step 6)");
          if (!empty($deletedXmlID)) {
            self::deleteCachedData($deletedXmlID, $cachedData);
            $needUpdate = true;
          }

          self::updateAgentStatus("start check update cache (step 7)");
          if (!empty($addedXmlID)) {
            self::addCachedData($addedXmlID, $cachedData);
            $needUpdate = true;
          }

          //cGeneral::dump([$deletedXmlID, $addedXmlID, $deletedOffersXmlID, $addedOffersXmlID, $updatedXmlID], '_cache_catalog_2');
          self::updateAgentStatus("start check update cache (step 8)");
          if ($needUpdate) {
            cGeneral::setJson($cachedData, cGeneral::getCacheDirectoryPath(), 'index.json');
            self::updateAgentStatus("ready updated cache");
          }else{
            self::updateAgentStatus("ready no need updated cache");
          }
        }

      }catch (\Exception $ex){
        cGeneral::dump([$ex->getTraceAsString()], '_cache_errors');
        self::updateAgentStatus("finish with error");
        return '\s34web\Mobile\Api\controllers\v1\classes\Agent::cacheCatalog(true);';
      }


      return '\s34web\Mobile\Api\controllers\v1\classes\Agent::cacheCatalog();';
    }

    public static function updateCachedData($xmlsID, &$cachedData)
    {
        $xmlByPages = [];
        $updateBlocks = [];
        foreach ($xmlsID as $xmlID) {
            foreach ($cachedData as $key => $block) {
                if(in_array($xmlID, $block['products'])){
                    $xmlByPages[$xmlID] = (int)$block['page'];
                    $updateBlocks[$block['page']] = $key;
                    break;
                }
            }
        }
        $xmlByPages = array_unique($xmlByPages);
        //cGeneral::dump([$xmlByPages, $updateBlocks], '_cache_catalog_u1');
        foreach ($xmlByPages as $xmlByPage) {
            $offset = ($xmlByPage - 1) * self::$limit;
            //cGeneral::dump([$xmlByPage, self::$limit, $offset], '_cache_catalog_u2');
            $indexData = cCatalog::cacheBlock(self::$limit, $offset);
            $cachedData[$updateBlocks[$xmlByPage]] = $indexData;
        }
    }

    public static function deleteCachedData($xmlsID, &$cachedData)
    {
        $xmlByPages = [];
        $updateBlocks = [];
        foreach ($xmlsID as $xmlID) {
            foreach ($cachedData as $blockKey => &$block) {
                foreach ($block['products'] as $key => $productXmlID) {
                    if ($xmlID == $productXmlID) {
                        unset($block['products'][$key]);
                        $block['count']--;
                        $xmlByPages[$xmlID] = (int)$block['page'];
                        $updateBlocks[$block['page']] = $blockKey;
                        break;
                    }
                }
            }
            unset($block);
        }
        $xmlByPages = array_unique($xmlByPages);
        //cGeneral::dump([$xmlByPages, $updateBlocks], '_cache_catalog_d1');
        $deleteOfferXmlID = [];
        foreach ($xmlByPages as $xmlID => $iNumPage) {
            $block = cGeneral::getJson(cGeneral::getCacheDirectoryPath() . '/block' . $iNumPage . '.json');
            //cGeneral::dump([count($block)/*, $block*/], '_cache_catalog_d2');
            foreach ($block as $key => $product) {
                if($product['xml_id'] == $xmlID){
                    $deleteOfferXmlID = array_merge($deleteOfferXmlID, array_column($product['offers'], 'xml_id'));
                    unset($block[$key]);
                    break;
                }
            }
            //cGeneral::dump([count($block)/*, $block*/], '_cache_catalog_d2');
            cGeneral::setJson($block, cGeneral::getCacheDirectoryPath(), 'block' . $iNumPage . '.json');
        }
        if(!empty($deleteOfferXmlID)){
            foreach ($deleteOfferXmlID as $xmlID) {
                foreach ($cachedData as &$block) {
                    foreach ($block['offers'] as $key => $offerXmlID) {
                        if ($xmlID == $offerXmlID) {
                            unset($block['offers'][$key]);
                        }
                    }
                }
            }
        }
        //cGeneral::dump([$deleteOfferXmlID], '_cache_catalog_d3');
    }

    public static function addCachedData($xmlsID, &$cachedData)
    {
        $xmlByPages = [];
        $updateBlocks = [];
        foreach ($xmlsID as $xmlID) {
            $added = false;
            foreach ($cachedData as $blockKey => &$block) {
                if (count($block['products']) < self::$limit) {
                    $block['products'][] = $xmlID;
                    $block['count']++;
                    $xmlByPages[(int)$block['page']] = $block['products'];
                    $updateBlocks[$block['page']] = $blockKey;
                    $added = true;
                    break;
                }
            }
            // todo доработать разбитие на блоки. Пока не додумался как
            if(!$added){
                $xmlByPages[$blockKey + 2][] = $xmlID;
                $updateBlocks[$blockKey + 2] = $blockKey + 1;
            }
        }
        //$xmlByPages = array_unique($xmlByPages);
        //cGeneral::dump([$xmlByPages, $updateBlocks], '_cache_catalog_a1');
        foreach ($xmlByPages as $iNumPage => $products) {
//            $offset = ($xmlByPage - 1) * self::$limit;
            //cGeneral::dump([$products], '_cache_catalog_a2');
            $indexData = cCatalog::cacheBlock(self::$limit, 0, ['page' => $iNumPage, 'xml_id' => $products]);
            $cachedData[$updateBlocks[$iNumPage]] = $indexData;
        }
    }
}