<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Восстановление пароля");
?><?$APPLICATION->IncludeComponent(
	"bitrix:main.auth.forgotpasswd", 
	".default", 
	array(
		"COMPONENT_TEMPLATE" => ".default",
		"AUTH_AUTH_URL" => "/auth/",
		"AUTH_REGISTER_URL" => "/auth/registration/"
	),
	false
);?><?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>