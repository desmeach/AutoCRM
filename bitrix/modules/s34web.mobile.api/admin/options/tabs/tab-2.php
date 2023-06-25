<?php

use Bitrix\Main\Localization\Loc;
use s34web\Mobile\Api\Init as Api;

$geoIpExist = Api::checkLibraryAvailability(Api::GEOIP);
?>

<tr>
    <td width='45%' valign='top'><?=Loc::getMessage('OPTION_USE_ONLY_HTTPS_EXCHANGE_TITLE')?><td>
    <td width='55%' valign='middle'>
        <?
        echo InputType('checkbox', 'OPTION_ONLY_HTTPS_EXCHANGE', 'Y', $options->getValue('ONLY_HTTPS_EXCHANGE'));
        ShowJSHint(Loc::getMessage('OPTION_ONLY_HTTPS_EXCHANGE_HINT'));
        ?>
    <td>
</tr>

<tr><td colspan="4">&nbsp;</td></tr>
<tr><td colspan="4">&nbsp;</td></tr>


<tr>
    <td width='45%' valign='top'><?=Loc::getMessage('OPTION_USE_AUTH_TOKEN_TITLE')?><td>
    <td width='55%' valign='middle'>
        <?
        echo InputType('checkbox', 'OPTION_USE_AUTH_TOKEN', 'Y', $options->getValue('USE_AUTH_TOKEN'));
        ShowJSHint(Loc::getMessage('OPTION_AUTH_TOKEN_HINT', ['#FIELD_NAME_RESTFUL_API_TOKEN#' => $options->getUserFieldCodeApiToken()], LANG));
        ?>
    <td>
</tr>

<tr>
    <td width='45%' valign='middle'><?=Loc::getMessage('OPTION_TOKEN_KEYWORD_TITLE')?><td>
    <td width='55%' valign='middle'>
        <input type='text' name='OPTION_TOKEN_KEYWORD' size='20' value='<?=$options->getValue('TOKEN_KEYWORD')?>'>
        <?ShowJSHint(Loc::getMessage('OPTION_TOKEN_KEYWORD_HINT'))?>
    <td>
</tr>

<tr>
    <td width='45%' valign='middle'><?=Loc::getMessage('TOKEN_GENERATE_TITLE')?><td>
    <td width='55%' valign='middle'>
        <a href="<?=str_replace('&generateToken=Y', '', $APPLICATION->GetCurUri())?>&generateToken=Y"><?=Loc::getMessage('GENERATE_LINK_TEXT')?></a>
        <?ShowJSHint(Loc::getMessage('TOKEN_GENERATE_HINT'))?>
    <td>
</tr>

<tr><td colspan="4">&nbsp;</td></tr>
<tr><td colspan="4">&nbsp;</td></tr>


<tr>
    <td width='45%' valign='middle'><?=Loc::getMessage('OPTION_USE_LIST_COUNTRY_FILTER')?><td>
    <td width='55%' valign='middle'>
        <?
        $ar = [
            'REFERENCE' => [
                Loc::getMessage('OPTION_USE_LIST_COUNTRY_FILTER_SELECT_TITLE_1'),
                Loc::getMessage('OPTION_USE_LIST_COUNTRY_FILTER_SELECT_TITLE_2')
            ],
            'REFERENCE_ID' => [
                Loc::getMessage('OPTION_USE_LIST_COUNTRY_FILTER_SELECT_ID_1'),
                Loc::getMessage('OPTION_USE_LIST_COUNTRY_FILTER_SELECT_ID_2')
            ]
        ];
        echo SelectBoxFromArray('OPTION_USE_LIST_COUNTRY_FILTER', $ar, $options->getValue('USE_LIST_COUNTRY_FILTER'), '', (!$geoIpExist) ? 'disabled' : '', false, $options->getFormName());
        ShowJSHint(Loc::getMessage('OPTION_USE_LIST_COUNTRY_FILTER_HINT'));
        ?>
    <td>
</tr>

<tr>
    <td width='45%' valign='top'><?=Loc::getMessage('OPTION_WHITE_LIST_COUNTRY_TITLE')?><td>
    <td width='55%' valign='middle'>
        <?php
        $text = (!$geoIpExist) ? 'Error: the '.Api::GEOIP.' library was not found' : $options->getValue('WHITE_LIST_COUNTRY');
        ?>
        <textarea name="OPTION_WHITE_LIST_COUNTRY" cols="50" rows="5" <?if(!$geoIpExist):?>disabled<?endif?> ><?=$text?></textarea>
        <?ShowJSHint(Loc::getMessage('OPTION_WHITE_LIST_COUNTRY_HINT'))?>
    <td>
</tr>


<tr><td colspan="4">&nbsp;</td></tr>
<tr><td colspan="4">&nbsp;</td></tr>


<tr>
    <td width='45%' valign='middle'><?=Loc::getMessage('OPTION_USE_BLACK_LIST_ADDRESS_FILTER')?><td>
    <td width='55%' valign='middle'>
        <?
        $ar = [
            'REFERENCE' => [
                Loc::getMessage('OPTION_USE_BLACK_LIST_ADDRESS_FILTER_SELECT_TITLE_1'),
                Loc::getMessage('OPTION_USE_BLACK_LIST_ADDRESS_FILTER_SELECT_TITLE_2')
            ],
            'REFERENCE_ID' => [
                Loc::getMessage('OPTION_USE_BLACK_LIST_ADDRESS_FILTER_SELECT_ID_1'),
                Loc::getMessage('OPTION_USE_BLACK_LIST_ADDRESS_FILTER_SELECT_ID_2')
            ]
        ];
        echo SelectBoxFromArray('OPTION_USE_BLACK_LIST_ADDRESS_FILTER', $ar, $options->getValue('USE_BLACK_LIST_ADDRESS_FILTER'), '', '', false, $options->getFormName());
        ShowJSHint(Loc::getMessage('OPTION_USE_BLACK_LIST_ADDRESS_FILTER_HINT'));
        ?>
    <td>
</tr>

<tr>
    <td width='45%' valign='top'><?=Loc::getMessage('OPTION_BLACK_LIST_ADDRESS_TITLE')?><td>
    <td width='55%' valign='middle'>
        <textarea name="OPTION_BLACK_LIST_ADDRESS" cols="50" rows="5"><?=$options->getValue('BLACK_LIST_ADDRESS')?></textarea>
        <?ShowJSHint(Loc::getMessage('OPTION_BLACK_LIST_ADDRESS_HINT'))?>
    <td>
</tr>


<tr><td colspan="4">&nbsp;</td></tr>
<tr><td colspan="4">&nbsp;</td></tr>


<tr>
    <td width='45%' valign='middle'><?=Loc::getMessage('OPTION_USE_WHITE_LIST_ADDRESS_FILTER')?><td>
    <td width='55%' valign='middle'>
        <?
        $ar = [
            'REFERENCE' => [
                Loc::getMessage('OPTION_USE_WHITE_LIST_ADDRESS_FILTER_SELECT_TITLE_1'),
                Loc::getMessage('OPTION_USE_WHITE_LIST_ADDRESS_FILTER_SELECT_TITLE_2')
            ],
            'REFERENCE_ID' => [
                Loc::getMessage('OPTION_USE_WHITE_LIST_ADDRESS_FILTER_SELECT_ID_1'),
                Loc::getMessage('OPTION_USE_WHITE_LIST_ADDRESS_FILTER_SELECT_ID_2')
            ]
        ];
        echo SelectBoxFromArray('OPTION_USE_WHITE_LIST_ADDRESS_FILTER', $ar, $options->getValue('USE_WHITE_LIST_ADDRESS_FILTER'), '', '', false, $options->getFormName());
        ShowJSHint(Loc::getMessage('OPTION_USE_WHITE_LIST_ADDRESS_FILTER_HINT'));
        ?>
    <td>
</tr>

<tr>
    <td width='45%' valign='top'><?=Loc::getMessage('OPTION_WHITE_LIST_ADDRESS_TITLE')?><td>
    <td width='55%' valign='middle'>
        <textarea name="OPTION_WHITE_LIST_ADDRESS" cols="50" rows="5"><?=$options->getValue('WHITE_LIST_ADDRESS')?></textarea>
        <?ShowJSHint(Loc::getMessage('OPTION_WHITE_LIST_ADDRESS_HINT'))?>
    <td>
</tr>


<tr><td colspan="4">&nbsp;</td></tr>
<tr><td colspan="4">&nbsp;</td></tr>


<tr>
    <td width='45%' valign='middle'><?=Loc::getMessage('OPTION_USE_ACCESS_CONTROL_ALLOW_ORIGIN_FILTER')?><td>
    <td width='55%' valign='middle'>
        <?
        $ar = [
            'REFERENCE' => [
                Loc::getMessage('OPTION_USE_ACCESS_CONTROL_ALLOW_ORIGIN_FILTER_SELECT_TITLE_1'),
                Loc::getMessage('OPTION_USE_ACCESS_CONTROL_ALLOW_ORIGIN_FILTER_SELECT_TITLE_2')
            ],
            'REFERENCE_ID' => [
                Loc::getMessage('OPTION_USE_ACCESS_CONTROL_ALLOW_ORIGIN_FILTER_SELECT_ID_1'),
                Loc::getMessage('OPTION_USE_ACCESS_CONTROL_ALLOW_ORIGIN_FILTER_SELECT_ID_2')
            ]
        ];
        echo SelectBoxFromArray('OPTION_USE_ACCESS_CONTROL_ALLOW_ORIGIN_FILTER', $ar, $options->getValue('USE_ACCESS_CONTROL_ALLOW_ORIGIN_FILTER'), '', '', false, $options->getFormName());
        ShowJSHint(Loc::getMessage('OPTION_USE_ACCESS_CONTROL_ALLOW_ORIGIN_FILTER_HINT'));
        ?>
    <td>
</tr>

<tr>
    <td width='45%' valign='top'><?=Loc::getMessage('OPTION_WHITE_LIST_DOMAIN_ACCESS_CONTROL_ALLOW_ORIGIN_TITLE')?><td>
    <td width='55%' valign='middle'>
        <textarea name="OPTION_WHITE_LIST_DOMAIN_ACCESS_CONTROL_ALLOW_ORIGIN" cols="50" rows="5"><?=$options->getValue('WHITE_LIST_DOMAIN_ACCESS_CONTROL_ALLOW_ORIGIN')?></textarea>
        <?ShowJSHint(Loc::getMessage('OPTION_WHITE_LIST_DOMAIN_ACCESS_CONTROL_ALLOW_ORIGIN_HINT'))?>
    <td>
</tr>