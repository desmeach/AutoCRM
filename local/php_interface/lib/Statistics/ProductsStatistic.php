<?php
/**
 * Created: 18.05.2023, 20:57
 * Author : Vladislav Naryzhny <desmeach@gmail.com>
 * Company: 34web Studio
 */

namespace lib\Statistics;

use lib\Controllers\OrdersController;
use lib\Controllers\ProductsController;

class ProductsStatistic {
    public static function getStatistic() {
        $products = ProductsController::getList();
        $orders = OrdersController::getList();
        $ordersProducts = [];
        foreach ($orders as $order) {
            array_push($ordersProducts, ...$order['PRODUCTS']['VALUE']);
        }
        foreach ($ordersProducts as $ordersProduct) {
            $ordersProductID = $ordersProduct['ID'];
            if (!isset($products[$ordersProductID]['ORDERS_COUNT']))
                $products[$ordersProductID]['ORDERS_COUNT'] = 1;
            else
                $products[$ordersProductID]['ORDERS_COUNT'] += 1;
        }
        $pairs = self::getPairsByApriori($orders);
        $relevantProducts = [];
        foreach ($pairs as $pair => $percantage) {
            $pair = explode(' => ', $pair);
            $relevantProducts[$pair[0]][] = ['product' => $pair[1], 'percantage' => $percantage];
        }
        foreach ($products as $id => $product) {
            $products[$id]['TOTAL_SUM'] = $product['PRICE']['VALUE'] * $product['ORDERS_COUNT'];
            if (array_key_exists($product['NAME'], $relevantProducts)) {
                $products[$id]['RELEVANT_PRODUCTS'] = $relevantProducts[$product['NAME']];
            }
        }
        return $products;
    }
    private static function getPairsByApriori($orders) {
        $ordersCount = count($orders);
        $minSupport = 0.5 * $ordersCount;

        $transsactions = self::getTranssactions($orders);
        $itemsFreq = self::getItemsFreq($transsactions);
        $fitFreq = self::getFitFreq($itemsFreq, $minSupport);
        $itemsPairs = self::getItemsPairs($fitFreq);
        $itemsPairsFreq = self::getItemsPairsFreq($itemsPairs, $transsactions);
        return self::convertFreqToPercentage($itemsPairsFreq, $ordersCount);
    }
    private static function getTranssactions($orders) {
        $transsactions = [];
        foreach ($orders as $order) {
            foreach ($order['PRODUCTS']['VALUE'] as $product) {
                $transsactions[$order['ID']][] = $product['NAME'];
            }
        }
        return $transsactions;
    }
    private static function getItemsFreq($transsactions) {
        $frequencies = [];
        foreach ($transsactions as $transsaction) {
            $count = array_count_values($transsaction);
            foreach ($count as $key => $value) {
                if (array_key_exists($key, $frequencies)) {
                    $frequencies[$key] += 1;
                }
                else {
                    $frequencies[$key] = 1;
                }
            }
        }
        return $frequencies;
    }
    private static function getFitFreq($frequencies, $minSupport) {
        $fitFreq = [];
        foreach ($frequencies as $key => $frequency) {
            if ($frequency >= $minSupport)
                $fitFreq[$key] = $frequency;
        }
        return $fitFreq;
    }
    private static function getItemsPairs($data) {
        $n = 0;
        $arr = [];
        foreach ($data as $key1 => $value1) {
            $m = 1;
            foreach ($data as $key2 => $value2) {
                $str = explode(' => ', $key2);
                for ($i = 0; $i < count($str); $i++) {
                    if (!strstr($key1, $str[$i]) && $m > $n + 1 && count($data) > $n + 1){
                        $arr[$key1 . " => " . $str[$i]] = 0;
                    }
                }
                $m++;
            }
            $n++;
        }
        return $arr;
    }
    private static function getItemsPairsFreq($itemsPairs, $data) {
        $arr = $itemsPairs;
        foreach ($itemsPairs as $key1 => $k) {
            foreach ($data as $key2 => $value) {
                $kk = explode(" => ", $key1);
                $jm = 0;
                for ($k = 0; $k < count($kk); $k++) {
                    for ($j = 0; $j < count($value); $j++) {
                        if ($value[$j] == $kk[$k]) {
                            $jm += 1;
                            break;
                        }
                    }
                }
                if ($jm > count($kk) - 1) {
                    $arr[$key1] += 1;
                }
            }
        }
        return $arr;
    }
    private static function getTriplets($itemsPairs) {
        $items = [];
        $i = 0;
        foreach ($itemsPairs as $itemsPair => $value) {
            $itemsPair = explode(' => ', $itemsPair);
            foreach ($itemsPair as $key => $item) {
                if (!array_key_exists($item, $items))
                    $items[$item] = $i++;
            }
        }
        $items = array_flip($items);
        $triplets = [];
        for ($i = 0; $i < count($items) - 2; $i++) {
            for ($j = $i + 1; $j < count($items) - 1; $j++) {
                for ($k = $j + 1; $k < count($items); $k++) {
                    $triplet = $items[$i] . ', ' . $items[$j] . ', ' . $items[$k];
                    if (!array_key_exists($triplet, $triplets))
                        $triplets[$triplet] = 0;
                }
            }
        }
        return $triplets;
    }
    private static function convertFreqToPercentage($itemsPairsFreq, $ordersCount) {
        $percantage = [];
        foreach ($itemsPairsFreq as $pair => $count) {
            $percantage[$pair] = $count / $ordersCount * 100 . "%";
        }
        return $percantage;
    }
}