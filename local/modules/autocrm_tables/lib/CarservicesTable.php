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

class CarservicesTable extends DataManager {
// название таблицы
    public static function getTableName()
    {
        return 'carservices';
    }

    public static function getMap()
    {
        return array(
            new IntegerField('ID', array(
                'autocomplete' => true,
                'primary' => true
            )),
            new StringField('NAME', array(
                'required' => true,
                'validation' => function () {
                    return array(
                        new Validator\Length(null, 255),
                    );
                },
            )),
            new StringField('DESCRIPTION', array(
                'required' => false,
                'validation' => function () {
                    return array(
                        new Validator\Length(null, 255),
                    );
                },
            )),
            new StringField('ADDRESS', array(
                'required' => false,
                'validation' => function () {
                    return array(
                        new Validator\Length(null, 255),
                    );
                }
            )),
            new StringField('LOCATION', array(
                'required' => false,
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
            new DatetimeField('UPDATED_AT',array(
                'required' => true)),
            new DatetimeField('CREATED_AT',array(
                'required' => true)),
        );
    }
}