<?php
/**
 * Created: 18.05.2023, 20:15
 * Author : Vladislav Naryzhny <desmeach@gmail.com>
 * Company: 34web Studio
 */

require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");

use lib\ReportsGenerator;

$filename = ReportsGenerator::getAnalyticReport($_POST['data'], $_POST['entity']);
echo $filename;