<?php
/**
 * Created: 08.03.2023, 19:25
 * Author : Vladislav Naryzhny <desmeach@gmail.com>
 * Company: 34web Studio
 */

namespace lib\Models;

use CIBlockElement;
use CModule;
use CUser;
use Exception;

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");
CModule::IncludeModule('iblock');
class ManagersModel extends Model {
    private static int $groupID = 5;
    public static function getList($filter): ?array {
        try {
            $filter['GROUPS_ID'] = self::$groupID;
            $filter['UF_KEY'] = getKey();
            $managersQueue = CUser::GetList(false, false, $filter);
            if ($managersQueue) {
                $managers  = [];
                while ($manager = $managersQueue->GetNext()) {
                    $managers[] = $manager;
                }
                return $managers;
            }
            return null;
        } catch(Exception $exception) {
            echo $exception->getMessage();
            return null;
        }
    }
    public static function getItemByID($ID): ?array {
        try {
            $itemList = CUser::GetByID($ID);
            if ($item = $itemList->GetNext())
                return $item;
            return null;
        } catch(Exception $exception) {
            echo $exception->getMessage();
            return null;
        }
    }
    public static function getListForDataTable($filter): bool|string|null {
        try {
            $items = self::getList($filter);
            $json = [];
            foreach ($items as $item) {
                $json[] = [
                    'id' => self::getItemDetailLink($item['ID'],
                        $item['ID'], 'managers'),
                    'name' => $item['NAME'] . " " . $item['LAST_NAME'] . " " . $item['SECOND_NAME'],
                    'email' => $item['EMAIL'],
                ];
            }
            return json_encode($json);
        } catch(Exception $exception) {
            echo $exception->getMessage();
            return null;
        }
    }
    public static function add($data) {
        global $USER;
        unset($data['IBLOCK_ID']);
        $PROPS = parent::formatFormRequest($data);
        $PROPS['GROUP_ID'] = [$PROPS['GROUP_ID']];
        $ID = $USER->Add($PROPS);
        return $ID ?? ['error' => 'Ошибка при создании элемента'];
    }
    public static function update($props): bool {
        return false;
    }
    public static function delete($ID): array {
        if(!CUser::Delete($_POST['ID'])) {
            return ['success' => '', 'error' => 'Не удалось удалить элемент!'];
        }
        return ['success' => 'Элемент удален', 'error' => ''];
    }
}