<?php
/**
 * Created: 20.06.2023, 16:29
 * Author : Vladislav Naryzhny <desmeach@gmail.com>
 * Company: 34web Studio
 */

namespace autocrm_tables\lib;

use Bitrix\Main\Entity\DataManager;
use Bitrix\Main\Entity\DatetimeField;
use Bitrix\Main\Entity\IntegerField;
use Bitrix\Main\Entity\StringField;
use Bitrix\Main\Entity\Validator;
use Bitrix\Main\ORM\Fields\Relations\Reference;
use Bitrix\Main\ORM\Query\Join;

class CarsTable extends DataManager {
    public static function getTableName() {
        return 'cars';
    }

    public static function getMap() {
        return [
            new IntegerField('ID', array(
                'autocomplete' => true,
                'primary' => true
            )),
            new StringField('VIN', array(
                'required' => true,
                'validation' => function () {
                    return array(
                        new Validator\Length(null, 17),
                    );
                },
                'unique' => true,
            )),
            new StringField('BRAND', array(
                'required' => false,
                'validation' => function () {
                    return array(
                        new Validator\Length(null, 50),
                    );
                }
            )),
            new StringField('MODEL', array(
                'required' => false,
                'validation' => function () {
                    return array(
                        new Validator\Length(null, 50),
                    );
                }
            )),
            new StringField('YEAR', array(
                'required' => false,
                'validation' => function () {
                    return array(
                        new Validator\Length(null, 4),
                    );
                }
            )),
            new StringField('MILEAGE', array(
                'required' => false,
                'validation' => function () {
                    return array(
                        new Validator\Length(null, 10),
                    );
                }
            )),
            new StringField('REG_NUM', array(
                'required' => false,
                'validation' => function () {
                    return array(
                        new Validator\Length(null, 10),
                    );
                }
            )),
            (new IntegerField('CLIENT_ID')),
            (new Reference(
                'CLIENT',
                ClientsTable::class,
                Join::on('this.CLIENT_ID', 'ref.ID')
            ))->configureJoinType('inner'),
            new StringField('KEY', array(
                'required' => true,
                'validation' => function () {
                    return array(
                        new Validator\Length(null, 255),
                    );
                }
            )),
            new DatetimeField('UPDATED_AT',array(
                'required' => true)),
            new DatetimeField('CREATED_AT',array(
                'required' => true)),
        ];
    }
}