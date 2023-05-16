<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Автосервисы");
?>
<?php
$APPLICATION->IncludeComponent(
	"autoCRM:dataTable", 
	".default", 
	array(
		"COMPONENT_TEMPLATE" => ".default",
		"ENTITY" => "branches",
		"COMPOSITE_FRAME_MODE" => "A",
		"COMPOSITE_FRAME_TYPE" => "DYNAMIC_WITH_STUB_LOADING"
	),
	false
);?>

<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>