<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetPageProperty("title", "CRM Авто");
$APPLICATION->SetPageProperty("NOT_SHOW_NAV_CHAIN", "Y");
$APPLICATION->SetTitle("CRM Авто");
global $USER;
if ($USER->IsAuthorized())
    LocalRedirect('/orders/');
?>

<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>