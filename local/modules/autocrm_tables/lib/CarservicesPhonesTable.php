<?php
/**
 * Created: 21.06.2023, 18:40
 * Author : Vladislav Naryzhny <desmeach@gmail.com>
 * Company: 34web Studio
 */

namespace autocrm_tables\lib;

use Bitrix\Main\Entity\DataManager;
use Bitrix\Main\Entity\DatetimeField;
use Bitrix\Main\Entity\IntegerField;
use Bitrix\Main\Entity\StringField;
use Bitrix\Main\ORM\Fields\Relations\Reference;
use Bitrix\Main\Entity\Validator;
use Bitrix\Main\ORM\Query\Join;

class CarservicesPhonesTable extends DataManager {
    public static function getTableName() {
        return 'carservices_phones';
    }

    public static function getMap() {
        return [
            new IntegerField('ID', array(
                'autocomplete' => true,
                'primary' => true
            )),
            new StringField('PHONE', array(
                'required' => true,
                'validation' => function () {
                    return array(
                        new Validator\Length(null, 255),
                    );
                }
            )),
            new StringField('KEY', array(
                'required' => true,
                'validation' => function () {
                    return array(
                        new Validator\Length(null, 255),
                    );
                }
            )),
            (new IntegerField('CARSERVICE_ID')),
            (new Reference(
                'CARSERVICE',
                CarservicesTable::class,
                Join::on('this.CARSERVICE_ID', 'ref.ID')
            ))->configureJoinType('inner'),
            new DatetimeField('UPDATED_AT',array(
                'required' => true)),
            new DatetimeField('CREATED_AT',array(
                'required' => true)),
        ];
    }
}