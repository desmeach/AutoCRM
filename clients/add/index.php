<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Добавление элемента");
?>

<?$APPLICATION->IncludeComponent(
	"autoCRM:tableDataEditing", 
	".default", 
	array(
		"COMPONENT_TEMPLATE" => ".default",
		"ENTITY" => "clients",
		"ACTION" => "add"
	),
	false
);?>

<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>