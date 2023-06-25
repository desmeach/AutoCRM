<?php

use Bitrix\Main\Localization\Loc;
?>

<tr>
    <td width='45%' valign='middle'><?=Loc::getMessage('OPTION_USE_RESTFUL_API')?><td>
    <td width='55%' valign='middle'>
        <?
        $ar = [
            'REFERENCE' => [
                Loc::getMessage('OPTION_USE_RESTFUL_API_SELECT_TITLE_1'),
                Loc::getMessage('OPTION_USE_RESTFUL_API_SELECT_TITLE_2')
            ],
            'REFERENCE_ID' => [
                Loc::getMessage('OPTION_USE_RESTFUL_API_SELECT_ID_1'),
                Loc::getMessage('OPTION_USE_RESTFUL_API_SELECT_ID_2')
            ]
        ];
        echo SelectBoxFromArray('OPTION_USE_RESTFUL_API', $ar, $options->getValue('USE_RESTFUL_API'), '', '', false, $options->getFormName());
        ShowJSHint(Loc::getMessage('OPTION_USE_RESTFUL_API_SELECT_HINT'));
        ?>
    <td>
</tr>

<tr>
    <td width='45%' valign='middle'><?=Loc::getMessage('OPTION_USE_VERSIONS')?><td>
    <td width='55%' valign='middle'>
        <?
        $ar = [
            'REFERENCE' => [
                Loc::getMessage('OPTION_USE_VERSIONS_SELECT_TITLE_1'),
                Loc::getMessage('OPTION_USE_VERSIONS_SELECT_TITLE_2')
            ],
            'REFERENCE_ID' => [
                Loc::getMessage('OPTION_USE_VERSIONS_SELECT_ID_1'),
                Loc::getMessage('OPTION_USE_VERSIONS_SELECT_ID_2')
            ]
        ];
        echo SelectBoxFromArray('OPTION_USE_VERSIONS', $ar, $options->getValue('USE_VERSIONS'), '', '', false, $options->getFormName());
        ShowJSHint(Loc::getMessage('OPTION_USE_VERSIONS_SELECT_HINT'));
        ?>
    <td>
</tr>

<tr>
    <td width='45%' valign='middle'><?=Loc::getMessage('OPTION_PATH_RESTFUL_API')?><td>
    <td width='55%' valign='middle'>
        <input type='text' name='OPTION_PATH_RESTFUL_API' size='9' value='<?=$options->getValue('PATH_RESTFUL_API')?>'>
        <?ShowJSHint(Loc::getMessage('OPTION_PATH_RESTFUL_HINT'))?>
    <td>
</tr>

<tr><td colspan="4">&nbsp;</td></tr>
<tr><td colspan="4">&nbsp;</td></tr>

<tr>
    <td width='45%' valign='middle'><?=Loc::getMessage('OPTION_OPERATING_MODE')?><td>
    <td width='55%' valign='middle'>
        <?
        $ar = [
            'REFERENCE' => [
                Loc::getMessage('OPTION_OPERATING_MODE_SELECT_TITLE_1'),
                Loc::getMessage('OPTION_OPERATING_MODE_SELECT_TITLE_2'),
                Loc::getMessage('OPTION_OPERATING_MODE_SELECT_TITLE_3')
            ],
            'REFERENCE_ID' => [
                Loc::getMessage('OPTION_OPERATING_MODE_SELECT_ID_1'),
                Loc::getMessage('OPTION_OPERATING_MODE_SELECT_ID_2'),
                Loc::getMessage('OPTION_OPERATING_MODE_SELECT_ID_3')
            ]
        ];
        echo SelectBoxFromArray('OPTION_OPERATING_MODE', $ar, $options->getValue('OPERATING_MODE'), '', '', false, $options->getFormName());
        ShowJSHint(Loc::getMessage('OPTION_OPERATING_MODE_HINT'));
        ?>
    <td>
</tr>