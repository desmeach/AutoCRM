<?php
/**
 * Created: 14.04.2023, 11:21
 * Author : Vladislav Naryzhny <desmeach@gmail.com>
 * Company: 34web Studio
 */

require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");

use lib\ReportsGenerator;

$filename = ReportsGenerator::getOrderReport($_POST['ID']);
echo $filename;