<?php
namespace s34web\Mobile\Api;

use s34web\Mobile\Api\controllers\v1\classes\cGeneral;

class Request extends Router
{
    public static function get()
    {
        $ar = [
            'date' => date('Y-m-d H:i:s'),
            'request_method' => parent::getMethod(),
            'ip_address' => parent::getRealIpAddr(),
            //'COUNTRY_CODE' => parent::getCountryCode(),
            'controller' => parent::getController(),
            'server_name' => $_SERVER["SERVER_NAME"],
            'is_test'=> cGeneral::accessByTest(),
            'action' => parent::getAction(),
            'parameters' => parent::getParameters()
        ];

        if (parent::getApiVersion()) {
            $ar['API_VERSION'] = parent::getApiVersion();
        }

        /*if ($_SERVER['HTTP_AUTHORIZATION_TOKEN']) {
            $ar['AUTHORIZATION_TOKEN'] = $_SERVER['HTTP_AUTHORIZATION_TOKEN'];
        }*/

        if ($_SERVER['HTTP_X_AUTH_TOKEN']) {
            $ar['AUTHORIZATION_TOKEN'] = $_SERVER['HTTP_X_AUTH_TOKEN'];
        }
        if ($_SERVER['HTTP_X_MOBILE_DEVICE_ID']) {
            $ar['DEVICE_ID'] = $_SERVER['HTTP_X_MOBILE_DEVICE_ID'];
        }
        if ($_SERVER['HTTP_X_AUTH_REFRESH_TOKEN']) {
            $ar['REFRESH_TOKEN'] = $_SERVER['HTTP_X_AUTH_REFRESH_TOKEN'];
        }
        return $ar;
    }
}