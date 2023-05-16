<?php
/**
 * Created: 29.04.2023, 11:26
 * Author : Vladislav Naryzhny <desmeach@gmail.com>
 * Company: 34web Studio
 */

namespace TablesModule\Tables;

use Bitrix\Main\Entity\DataManager;
use Bitrix\Main\Entity\EnumField;
use Bitrix\Main\Entity\IntegerField;
use Bitrix\Main\Entity\ReferenceField;
use Bitrix\Main\Entity\StringField;
use Bitrix\Main\SystemException;

class ClientsTable extends DataManager {
    public static function getTableName() {
        return 'clients';
    }

    public static function getUfId() {
        return 'CLIENTS';
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
            new StringField('NAME', [
                'required' => true,
            ]),
            new EnumField('GENDER', [
                'values' => ['М', 'Ж'],
            ]),
            new IntegerField('CAR_ID'),
            new ReferenceField(
                'CAR',
                '\TablesModule\Tables\CarsTable',
                array('=this.CAR_ID' => 'ref.ID')
            )
        ];
    }
}