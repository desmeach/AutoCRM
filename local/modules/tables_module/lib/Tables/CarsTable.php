<?php
/**
 * Created: 29.04.2023, 19:11
 * Author : Vladislav Naryzhny <desmeach@gmail.com>
 * Company: 34web Studio
 */

namespace TablesModule\Tables;

use Bitrix\Main\Entity\DataManager;
use Bitrix\Main\Entity\EnumField;
use Bitrix\Main\Entity\IntegerField;
use Bitrix\Main\Entity\StringField;
use Bitrix\Main\SystemException;
class CarsTable extends DataManager {
    public static function getTableName() {
        return 'cars';
    }

    public static function getUfId() {
        return 'CARS';
    }

    /**
     * @throws SystemException
     */
    public static function getMap() {
        return [
            new IntegerField('ID', [
                'primary' => true,
                'autocomplete' => true,
            ]),
            new StringField('VIN', [
                'required' => true,
            ]),
        ];
    }
}