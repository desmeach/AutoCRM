<?php
/**
 * Created: 18.04.2023, 21:59
 * Author : Vladislav Naryzhny <desmeach@gmail.com>
 * Company: 34web Studio
 */

namespace lib\Models;

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

use CIBlockElement;
use CModule;
use Exception;

CModule::IncludeModule('iblock');

abstract class Model {
    abstract static function getList($filter);
    abstract static function getItemByID($ID): ?array;
    abstract static function add($data);
    abstract static function delete($ID);
    protected static function getItemByIDFromIBlockID($IBLOCK_ID, $ID): array|\_CIBElement|null {
        try {
            $itemsList = CIBlockElement::GetList(false, [
                    'IBLOCK_ID' => $IBLOCK_ID,
                    'ID' => $ID,
                    'PROPERTY_KEY_VALUE' => getKey(),
                ]
            );
            return $itemsList->GetNextElement();
        } catch (Exception $exception) {
            echo $exception->getMessage();
            return null;
        }
    }
    protected static function getListByIBlockID($IBLOCK_ID, $filter): int|\CIBlockResult|null {
        try {
            $filter['IBLOCK_ID'] = $IBLOCK_ID;
            $filter['PROPERTY_KEY'] = getKey();
            $itemsList = CIBlockElement::GetList(false, $filter);
            if (!$itemsList)
                return null;
            return $itemsList;
        } catch(Exception $exception) {
            echo $exception->getMessage();
            return null;
        }
    }
    protected static function getLinkedItemsList($IBLOCK_ID, $IDs): array {
        $itemsList = CIBlockElement::GetList(Array(), [
                'IBLOCK_ID' => $IBLOCK_ID,
                'ID' => $IDs,
                'PROPERTY_KEY_VALUE' => getKey(),
            ]
        );
        $items = [];
        while ($item = $itemsList->GetNext()) {
            $items[] = $item;
        }
        return $items;
    }
    protected static function formatArrayToNumericListStr($array, $itemSection): string {
        $res = "";
        foreach ($array as $i => $value) {
            $i++;
            $res .= "$i. " . self::getItemDetailLink($value['ID'], $value['NAME'], $itemSection);
        }
        return $res;
    }
    protected static function getItemDetailLink($ID, $value, $itemSection): string {
        $page = "/$itemSection/detail/index.php?ID=$ID";
        return "<a style='text-decoration: none; color: black;' href='$page'> $value </a><br>";
    }
    protected static function formatFormRequest($request): array {
        $PROPS = [];
        foreach ($request as $code => $value) {
            if (is_array($value)) {
                $PROPS[$code] = [];
                foreach ($value as $subValue) {
                    $subValue = is_array($subValue) ? $subValue[array_key_first($subValue)] : $subValue;
                    $PROPS[$code][] = $subValue;
                }
            } else {
                $PROPS[$code] = $value;
            }
        }
        return $PROPS;
    }
    public static function addItem($data, $IBLOCK_ID) {
        global $USER;
        $props = self::formatFormRequest($data);
        $name = $props['NAME'];
        unset($props['NAME']);
        $el = new CIBlockElement;
        $arFields = [
            'MODIFIED_BY' => $USER->GetID(),
            'NAME' => $name,
            'IBLOCK_ID' => $IBLOCK_ID,
            'PROPERTY_VALUES' => $props
        ];
        return $el->Add($arFields);
    }
    protected static function deleteElem($ID): array {
        if(!CIBlockElement::Delete($ID)) {
            return ['success' => '', 'error' => 'Не удалось удалить элемент!'];
        }
        return ['success' => 'Элемент удален', 'error' => ''];
    }
    protected static function updateElem($IBLOCK_ID) {
        global $USER;
        $NAME = $_POST['NAME'];
        $ID = $_POST['ID'];
        unset($_POST['IBLOCK_ID'], $_POST['ID']);
        $PROPS = self::formatFormRequest($_POST);

        $el = new CIBlockElement;

        $arFields = [
            'MODIFIED_BY' => $USER->GetID(),
            'NAME' => $NAME,
            'PROPERTY_VALUES' => $PROPS
        ];

        if ($ID = $el->Update($ID, $arFields, false, false))
            return json_encode(['success' => $ID]);
        else
            return json_encode(['error' => $el->LAST_ERROR]);
    }
}