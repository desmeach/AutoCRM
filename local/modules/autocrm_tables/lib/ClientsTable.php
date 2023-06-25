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
use Bitrix\Main\ORM\Fields\Relations\ManyToMany;

class ClientsTable extends DataManager {
// название таблицы
    public static function getTableName()
    {
        return 'clients';
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
            new StringField('PHONE', array(
                'required' => true,
                'validation' => function () {
                    return array(
                        new Validator\Length(null, 12),
                    );
                },
                'unique' => true,
            )),
            new StringField('EMAIL', array(
                'required' => false,
                'validation' => function () {
                    return array(
                        new Validator\Length(null, 255),
                    );
                }
            )),
            (new ManyToMany('CARSERVICES', CarservicesTable::class))
                ->configureTableName('clients_carservices'),
            new DatetimeField('UPDATED_AT',array(
                'required' => true)),
            new DatetimeField('CREATED_AT',array(
                'required' => true)),
        );
    }
}