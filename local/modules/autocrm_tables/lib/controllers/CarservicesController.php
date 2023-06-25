<?php
/**
 * Created: 20.06.2023, 18:34
 * Author : Vladislav Naryzhny <desmeach@gmail.com>
 * Company: 34web Studio
 */

namespace autocrm_tables\lib\controllers;

use autocrm_tables\lib\CarservicesPhonesTable;
use autocrm_tables\lib\CarservicesTable;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\ObjectPropertyException;
use Bitrix\Main\SystemException;

class CarservicesController extends Controller {
    private const REQUIRED_PROPERTIES = [
        'NAME' => 'string',
        'CREATED_AT' => 'datetime',
        'UPDATED_AT' => 'datetime',
        'KEY' => 'string',
    ];
    private const NOT_REQUIRED_PROPERTIES = [
        'DESCRIPTION' => 'text',
        'ADDRESS' => 'string',
        'LOCATION' => 'string',
    ];

    /**
     * @param $data
     * @return bool|string
     */
    public static function add($data): bool|string {
        return false;
    }

    /**
     * @params $key - secret string to filter carservices on current user
     * @throws ObjectPropertyException
     * @throws SystemException
     * @throws ArgumentException
     */
    public static function getList($key): bool|array {
        $carservices = CarservicesTable::getList([
            'select' => ['ID', 'NAME', 'LOCATION', 'ADDRESS', 'DESCRIPTION'],
            'filter' => ['=KEY' => $key],
        ])->fetchAll();
        $phones = CarservicesPhonesTable::getList([
            'select' => ['CARSERVICE_ID', 'PHONE'],
            'filter' => ['=KEY' => $key],
        ])->fetchAll();
        $carservicePhones = [];
        foreach ($phones as $phone) {
            $carservicePhones[$phone['CARSERVICE_ID']][] = $phone['PHONE'];
        }
        foreach ($carservices as $i => $carservice) {
            $carservices[$i]['PHONES'] = $carservicePhones[$carservice['ID']];
        }
        return $carservices;
    }

    /**
     * @throws ObjectPropertyException
     * @throws SystemException
     * @throws ArgumentException
     */
    public static function getById($id): ?array {
        $carservice = CarservicesTable::getById($id)->fetch();
        if ($carservice)
            return $carservice;
        else
            return null;
    }

//    /**
//     * Add one more user link to carservice
//     * @return void
//     */
//    public static function addNewUserKey($id, $key) {
//        ClientsTable::update();
//    }

    public static function getRequiredProps(): array {
        return self::REQUIRED_PROPERTIES;
    }

    public static function getProps(): array {
        return array_merge(self::REQUIRED_PROPERTIES, self::NOT_REQUIRED_PROPERTIES);
    }
}