<?php
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Изменение элемента");
?>

<?php $APPLICATION->IncludeComponent(
	"autoCRM:tableDataEditing", 
	".default", 
	array(
		"COMPONENT_TEMPLATE" => ".default",
		"ENTITY" => "products",
		"ACTION" => "edit"
	),
	false
);?>

<?php require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>