<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
	<div class="fields string" id="main_<?=$arParams["arUserField"]["FIELD_NAME"]?>"><?
foreach ($arResult["VALUE"] as $res):
?><div class="fields string"><?
	if($arParams["arUserField"]["SETTINGS"]["ROWS"] < 2):
?><input
		<?php if ($arParams["arUserField"]["FIELD_NAME"] != 'UF_KEY'):?>
		type="text"
		<?php else: ?>
		type="hidden"
		<?php endif;?>
		name="<?=$arParams["arUserField"]["FIELD_NAME"]?>"
		<?php if ($arParams["arUserField"]["FIELD_NAME"] != 'UF_KEY'):?>
		value="<?=$res?>"
		<?php else: ?>
		value="<?=\Bitrix\Main\Security\Random::getString(20)?>"
		<?php endif;?>
		<?
	if (intval($arParams["arUserField"]["SETTINGS"]["SIZE"]) > 0):
		?> size="<?=$arParams["arUserField"]["SETTINGS"]["SIZE"]?>"<?
	endif;
	if (intval($arParams["arUserField"]["SETTINGS"]["MAX_LENGTH"]) > 0):
		?> maxlength="<?=$arParams["arUserField"]["SETTINGS"]["MAX_LENGTH"]?>"<?
	endif;
?> class="fields string form-control"><?
	else:
?><textarea class="fields string form-control" name="<?=$arParams["arUserField"]["FIELD_NAME"]?>"<?
	?> cols="<?=$arParams["arUserField"]["SETTINGS"]["SIZE"]?>"<?
	?> rows="<?=$arParams["arUserField"]["SETTINGS"]["ROWS"]?>" <?
	if (intval($arParams["arUserField"]["SETTINGS"]["MAX_LENGTH"]) > 0):
		?> maxlength="<?=$arParams["arUserField"]["SETTINGS"]["MAX_LENGTH"]?>"<?
	endif;
	if ($arParams["arUserField"]["EDIT_IN_LIST"]!="Y"):
		?> disabled="disabled"<?
	endif;
?>><?=$res?></textarea><?
	endif;
?></div><?
endforeach;
?></div>
<?if ($arParams["arUserField"]["MULTIPLE"] == "Y" && $arParams["SHOW_BUTTON"] != "N"):?>
<input type="button" value="<?=GetMessage("USER_TYPE_PROP_ADD")?>" onClick="addElement('<?=$arParams["arUserField"]["FIELD_NAME"]?>', this)">
<?endif;?>