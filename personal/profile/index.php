<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Настройки пользователя");
?>

<?$APPLICATION->IncludeComponent("bitrix:main.profile", "crm_personal", Array(
	"SET_TITLE" => "Y",	// Устанавливать заголовок страницы
	),
	false
);?>

<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>