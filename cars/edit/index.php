<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Изменение элемента");
?>

<?$APPLICATION->IncludeComponent(
	"autoCRM:tableDataEditing", 
	".default", 
	array(
		"COMPONENT_TEMPLATE" => ".default",
		"ENTITY" => "cars",
		"ACTION" => "edit"
	),
	false
);?>

<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>