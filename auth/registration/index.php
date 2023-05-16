<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Регистрация");
?>
<?$APPLICATION->IncludeComponent("bitrix:main.register", "crm_register", Array(
	"AUTH" => "Y",	// Автоматически авторизовать пользователей
		"REQUIRED_FIELDS" => array(	// Поля, обязательные для заполнения
			0 => "EMAIL",
			1 => "NAME",
			2 => "SECOND_NAME",
			3 => "LAST_NAME",
			4 => "PERSONAL_BIRTHDAY",
			5 => "PERSONAL_PHONE",
		),
		"SET_TITLE" => "Y",	// Устанавливать заголовок страницы
		"SHOW_FIELDS" => array(	// Поля, которые показывать в форме
			0 => "EMAIL",
			1 => "NAME",
			2 => "SECOND_NAME",
			3 => "LAST_NAME",
			4 => "PERSONAL_BIRTHDAY",
			5 => "PERSONAL_PHONE",
		),
		"SUCCESS_PAGE" => "",	// Страница окончания регистрации
		"USER_PROPERTY" => array(	// Показывать доп. свойства
			0 => "UF_CARSERVICE",
			1 => "UF_KEY",
		),
		"USER_PROPERTY_NAME" => "",	// Название блока пользовательских свойств
		"USE_BACKURL" => "Y",	// Отправлять пользователя по обратной ссылке, если она есть
		"COMPONENT_TEMPLATE" => ".default"
	),
	false
);?>
<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>