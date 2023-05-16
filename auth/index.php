<?php require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
global $APPLICATION;
$APPLICATION->SetTitle(""); ?>

<?php $APPLICATION->IncludeComponent(
	"bitrix:system.auth.form", 
	"auth_crm",
	array(
		"COMPONENT_TEMPLATE" => "auth_crm",
		"FORGOT_PASSWORD_URL" => "forgot/",
		"PROFILE_URL" => "/personal/profile/",
		"REGISTER_URL" => "registration/",
		"SHOW_ERRORS" => "N"
	),
	false
);?>

<?php require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>