<?php
/**
 * Created: 20.06.2023, 18:34
 * Author : Vladislav Naryzhny <desmeach@gmail.com>
 * Company: 34web Studio
 */

namespace autocrm_tables\lib\controllers;

use autocrm_tables\lib\ClientsTable;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\ObjectPropertyException;
use Bitrix\Main\SystemException;
use Bitrix\Main\Type\DateTime;
use Exception;

class ClientsController extends Controller {
    private const REQUIRED_PROPERTIES = [
        'NAME' => 'string',
        'PHONE' => 'array',
        'CREATED_AT' => 'datetime',
        'UPDATED_AT' => 'datetime',
        'KEY' => 'string',
    ];
    private const NOT_REQUIRED_PROPERTIES = [
        'EMAIL' => 'string',
    ];

    /**
     * @param $data
     * @return bool|string
     * @throws ArgumentException
     * @throws ObjectPropertyException
     * @throws SystemException
     * @throws Exception
     */
    public static function add($data): bool|string {
        if (!isset($data['PHONE']))
            return json_encode([
                'success' => false,
                'error' => 'Не передано обязательное свойство PHONE'
            ]);
        if (self::getByPhone($data['PHONE'])) {
            return json_encode([
                'success' => false,
                'error' => 'Пользователь с таким номером телефона уже существует'
            ]);
        }
        $resultData = [];
        $currentDate = new DateTime();
        $data['UPDATED_AT'] = $currentDate;
        $data['CREATED_AT'] = $currentDate;
        foreach (self::REQUIRED_PROPERTIES as $requiredProp) {
            if (!isset($data[$requiredProp]))
                return json_encode([
                    'success' => false,
                    'error' => 'Не передано обязательное свойство ' . $requiredProp
                ]);
            $resultData[$requiredProp] = $data[$requiredProp];
        }
        foreach (self::NOT_REQUIRED_PROPERTIES as $notRequiredProp) {
            if (!isset($data[$notRequiredProp]))
                continue;
            $resultData[$notRequiredProp] = $data[$notRequiredProp];
        }

        $result = ClientsTable::add($resultData);
        if ($result->isSuccess()) {
            return json_encode([
                'success' => [
                    'id' => $result->getId()
                ],
                'error' => ''
            ]);
        } else {
            return json_encode([
                'success' => false,
                'error' => $result->getErrorMessages()
            ]);
        }
    }

    /**
     * @params $phone - user's phone number
     * @throws ObjectPropertyException
     * @throws SystemException
     * @throws ArgumentException
     */
    public static function getByPhone($phone): bool|array {
        return ClientsTable::getList([
            'select' => ['ID'],
            'filter' => ['=PHONE' => $phone]
        ])->fetch();
    }

    /**
     * Add one more user link to carservice
     * @return void
     */
    public static function addNewUserKey($id, $key) {
//        ClientsTable::update();
    }

    static function getList($key) {
        // TODO: Implement getList() method.
    }

    static function getById($id) {
        // TODO: Implement getById() method.
    }
    public static function getRequiredProps(): array {
        return self::REQUIRED_PROPERTIES;
    }

    public static function getProps(): array {
        return array_merge(self::REQUIRED_PROPERTIES, self::NOT_REQUIRED_PROPERTIES);
    }
}