<?php
/**
 * Created: 29.04.2023, 11:02
 * Author : Vladislav Naryzhny <desmeach@gmail.com>
 * Company: 34web Studio
 */

if(!check_bitrix_sessid()) return;

echo CAdminMessage::ShowNote("Модуль dv_module установлен");
