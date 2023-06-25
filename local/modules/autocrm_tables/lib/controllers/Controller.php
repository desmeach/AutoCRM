<?php
/**
 * Created: 23.06.2023, 14:57
 * Author : Vladislav Naryzhny <desmeach@gmail.com>
 * Company: 34web Studio
 */

namespace autocrm_tables\lib\controllers;

abstract class Controller {
    abstract static function add($data);
    abstract static function getList($key);
    abstract static function getById($id);
    abstract static function getRequiredProps();
    abstract static function getProps();

}