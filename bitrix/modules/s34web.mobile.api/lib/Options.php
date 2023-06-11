<?php
namespace s34web\Mobile\Api;

use \Bitrix\Main\Config\Option;
use \Bitrix\Main\Localization\Loc;
use CUserTypeEntity;
use CUser;

class Options
{
    const MODULE_ID = 's34web.mobile.api';
    const FORM_NAME = 'options';
    const OPTION_CODE_PREFIX = 'OPTION_';

    const USER_FIELD_CODE_API_TOKEN = 'UF_REST_API_TOKEN';

    private $options;

    // Options

    public function save($data)
    {
        $this->extractOptions($data);

        if ($ar = $this->getOptions()) {
            foreach ($ar as $code => $value) {

                switch ($code) {

                    case 'WHITE_LIST_ADDRESS':
                        $value = str_replace([' ', 'http://', 'https://'], '', $value);
                        break;
                    case 'BLACK_LIST_ADDRESS':
                        $value = str_replace([' ', 'http://', 'https://'], '', $value);
                        break;
                    default:
                        $value = trim($value);
                }

                Option::set($this->getModuleId(), $code, $value);
            }
            return true;
        }
        return false;
    }

    public function restore()
    {
        Option::delete($this->getModuleId());
        return true;
    }

    private function extractOptions($data)
    {
        $arResult = [];

        // Checkbox
        if (!isset($data['OPTION_ONLY_HTTPS_EXCHANGE'])) {
            $data['OPTION_ONLY_HTTPS_EXCHANGE'] = 'N';
        }
        if (!isset($data['OPTION_USE_AUTH_TOKEN'])) {
            $data['OPTION_USE_AUTH_TOKEN'] = 'N';
        }

        // Update User field for token
        $this->userFieldToken($data['OPTION_USE_AUTH_TOKEN']);

        foreach ($data as $k => $v) {
            if (preg_match('/^'.self::OPTION_CODE_PREFIX.'/', strtoupper($k))) {
                $arResult[str_replace(self::OPTION_CODE_PREFIX, '', $k)] = $v;
            }
        }

        $this->setOptions($arResult);
    }

    public function getValue($option)
    {
        return Option::get($this->getModuleId(), $option);
    }

    public function getModuleId()
    {
        return self::MODULE_ID;
    }

    public function getFormName()
    {
        return self::FORM_NAME;
    }

    private function getOptions()
    {
        return $this->options;
    }

    private function setOptions($options)
    {
        $this->options = $options;
    }

    // Additional method

    public function getUserFieldCodeApiToken()
    {
        return self::USER_FIELD_CODE_API_TOKEN;
    }

    public function userFieldToken($flag)
    {
        if ($flag == 'Y') {
            // Create user field for token
            $arFields = [
                'ENTITY_ID' => 'USER',
                'FIELD_NAME' => self::USER_FIELD_CODE_API_TOKEN,
                'USER_TYPE_ID' => 'string',
                'SORT' => 100,
                'MULTIPLE' => 'N',
                'MANDATORY' =>  'N',
                'SHOW_FILTER' => 'I',
                'SHOW_IN_LIST' => 'Y',
                'EDIT_IN_LIST' => 'Y',
                'IS_SEARCHABLE' => 'N',
                'SETTINGS' => [
                    'SIZE' => 40,
                    'ROWS' => 1,
                    'REGEXP' => '',
                    'MIN_LENGTH' => 0,
                    'MAX_LENGTH' => 0,
                    'DEFAULT_VALUE' => ''
                ]
            ];

            $obUserField  = new CUserTypeEntity;
            $obUserField->Add($arFields);
        }
    }
    public function generateTokens()
    {
        $user = new CUser();
        $counter = 0;

        // Get list users with empty token field

        $arFilter = [
            'ACTIVE' => 'Y',
            self::USER_FIELD_CODE_API_TOKEN => false
        ];

        $arSelect = [
            'FIELDS' => ['ID', 'LOGIN']
        ];
        $by = "ID";
        $order = "DESC";
        if ($users = CUser::GetList($by, $order, $arFilter, $arSelect)) {

            while ($ar = $users->fetch()) {

                $id = $ar['ID'];

                // Set token for users

                $token = md5($ar['ID'].'-'.$ar['LOGIN'].'='.date('Y-m-d H:i:s'));
                $token = str_split($token, 8);
                $token = implode('-', $token);

                $arFields = [
                    self::USER_FIELD_CODE_API_TOKEN => $token
                ];

                $user->update($id, $arFields);

                $counter++;
            }
        }

        return $counter;
    }
}