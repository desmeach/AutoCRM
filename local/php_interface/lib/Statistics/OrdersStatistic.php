<?php
/**
 * Created: 17.03.2023, 0:43
 * Author : Vladislav Naryzhny <desmeach@gmail.com>
 * Company: 34web Studio
 */

namespace lib\Statistics;

use lib\Controllers\ClientsController;
use lib\Controllers\OrdersController;

class OrdersStatistic {
    public static function getStatistic(): array {
        $clients = ClientsController::getList();
        $orders = OrdersController::getList();
        foreach ($orders as $order) {
            $clientID = $order['CLIENT']['VALUE']['ID'];
            $clients[$clientID]['ORDERS_COUNT'] = isset($clients[$clientID]['ORDERS_COUNT']) ?
                $clients[$clientID]['ORDERS_COUNT'] + 1 : 1;
            $totalPrice = $order['TOTAL_PRICE']['VALUE'];
            if (is_numeric($totalPrice))
                $clients[$clientID]['ORDERS_PRICE_SUMMARY'] = isset($clients[$clientID]['ORDERS_PRICE_SUMMARY']) ?
                    $clients[$clientID]['ORDERS_PRICE_SUMMARY'] + $totalPrice : $totalPrice;
            foreach ($order['PRODUCTS']['VALUE'] as $product) {
                $clients[$clientID]['PRODUCTS_COUNT'][] = $product['NAME'];
            }
        }
        foreach ($clients as $i => $client) {
            if (!$client['PRODUCTS_COUNT'])
                continue;
            $clients[$i]['PRODUCTS_COUNT'] = array_count_values($client['PRODUCTS_COUNT']);
            arsort($clients[$i]['PRODUCTS_COUNT']);
            $clients[$i]['PRODUCTS_COUNT'] = array_slice($clients[$i]['PRODUCTS_COUNT'], 0, 3);
        }
        return $clients;
    }
}