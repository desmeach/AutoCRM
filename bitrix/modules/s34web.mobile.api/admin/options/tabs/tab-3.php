<?php

use Bitrix\Main\Localization\Loc;
?>

<tr>
    <td width='45%' valign='middle'><?=Loc::getMessage('SUPPORT_LINK_TITLE')?><td>
    <td width='55%' valign='middle'>
        <a href="<?=Loc::getMessage('SUPPORT_LINK')?>" target="_blank"><?=Loc::getMessage('SUPPORT_LINK_TEXT')?></a>
        <?ShowJSHint(Loc::getMessage('SUPPORT_LINK_HINT'))?>
    <td>
</tr>

<tr>
    <td width='45%' valign='middle'><?=Loc::getMessage('SUPPORT_DOCUMENTATION_LINK_TITLE')?><td>
    <td width='55%' valign='middle'>
        <a href="<?=Loc::getMessage('SUPPORT_DOCUMENTATION_LINK')?>" target="_blank"><?=Loc::getMessage('SUPPORT_DOCUMENTATION_LINK_TEXT')?></a>
        <?ShowJSHint(Loc::getMessage('SUPPORT_DOCUMENTATION_LINK_HINT'))?>
    <td>
</tr>

<tr>
    <td width='45%' valign='middle'><?=Loc::getMessage('SUPPORT_FEEDBACK_LINK_TITLE')?><td>
    <td width='55%' valign='middle'>
        <a href="<?=Loc::getMessage('SUPPORT_FEEDBACK_LINK')?>" target="_blank"><?=Loc::getMessage('SUPPORT_FEEDBACK_LINK_TEXT')?></a>
    <?ShowJSHint(Loc::getMessage('SUPPORT_FEEDBACK_LINK_HINT'))?>
    <td>
</tr>

<tr><td colspan="4">&nbsp;</td></tr>
<tr><td colspan="4">&nbsp;</td></tr>

<tr>
    <td width='45%' valign='middle'><?=Loc::getMessage('OPTION_SUPPORT_USE_LOG_TITLE')?><td>
    <td width='55%' valign='middle'>
        <?
        $ar = [
            'REFERENCE'    => [
                Loc::getMessage('OPTION_SUPPORT_USE_LOG_SELECT_TITLE_1'),
                Loc::getMessage('OPTION_SUPPORT_USE_LOG_SELECT_TITLE_2')
            ],
            'REFERENCE_ID' => [
                Loc::getMessage('OPTION_SUPPORT_USE_LOG_SELECT_ID_1'),
                Loc::getMessage('OPTION_SUPPORT_USE_LOG_SELECT_ID_2')
            ]
        ];
        echo SelectBoxFromArray('OPTION_SUPPORT_USE_LOG', $ar, $options->getValue('SUPPORT_USE_LOG'), '', '', false, $options->getFormName());
        ShowJSHint(Loc::getMessage('OPTION_SUPPORT_USE_LOG_HINT'));
        ?>
    <td>
</tr>

<tr>
    <td width='45%' valign='middle'><?=Loc::getMessage('OPTION_SUPPORT_LOG_PATH_TITLE')?><td>
    <td width='55%' valign='middle'>
        <input type='text' name='OPTION_SUPPORT_LOG_PATH' size='40' value='<?=$options->getValue('SUPPORT_LOG_PATH')?>'>
        <?ShowJSHint(Loc::getMessage('OPTION_SUPPORT_LOG_PATH_HINT'))?>
    <td>
</tr>