<?php require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Авторизация"); ?>

<?$APPLICATION->IncludeComponent(
	"bitrix:system.auth.form", 
	"auth_crm", 
	array(
		"COMPONENT_TEMPLATE" => "auth_crm",
		"FORGOT_PASSWORD_URL" => "forget/",
		"PROFILE_URL" => "/personal/profile/",
		"REGISTER_URL" => "registration/",
		"SHOW_ERRORS" => "N"
	),
	false
);?><?php require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>