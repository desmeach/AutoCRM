<?php

use Bitrix\Main\Localization\Loc;
use s34web\Mobile\Api\Options;

$module_id = "s34web.mobile.api";
\Bitrix\Main\Loader::IncludeModule($module_id);
IncludeModuleLangFile($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/options.php");
IncludeModuleLangFile(__FILE__);

global $USER,$APPLICATION;
$RIGHT_W = $RIGHT_R = $USER->IsAdmin();
if($RIGHT_R || $RIGHT_W) :

$options = new Options();

$tabControl = new CAdminTabControl(
    'tabControl',
    [
        ['DIV' => 'tab-1', 'TAB' => Loc::getMessage('TAB_MAIN_TITLE'), 'TITLE' => Loc::getMessage('TAB_MAIN_DESCRIPTION')],
        ['DIV' => 'tab-2', 'TAB' => Loc::getMessage('TAB_SECURITY_TITLE'), 'TITLE' => Loc::getMessage('TAB_SECURITY_DESCRIPTION')],
        ['DIV' => 'tab-3', 'TAB' => Loc::getMessage('TAB_SUPPORT_TITLE'), 'TITLE' => Loc::getMessage('TAB_SUPPORT_DESCRIPTION')]
    ]
);

$APPLICATION->SetTitle(Loc::getMessage('PAGE_TITLE') . ' options.php' .Loc::getMessage('API_MODULE_NAME').'"');

if ($_POST) {

    $data = &$_POST;

    if ($data['options-save']) {
        if ($options->save($data)) {
            CAdminMessage::ShowNote(Loc::getMessage('OPTIONS_SAVED'));
        }
    }

    if ($data['options-restore']) {
        if ($options->restore()) {
            CAdminMessage::ShowNote(Loc::getMessage('OPTIONS_RESTORED'));
        }
    }
}

if ($_GET['generateToken'] == 'Y') {
    $count = $options->generateTokens();

    if ($count > 0) {
        CAdminMessage::ShowNote(Loc::getMessage('TOKENS_GENERATED', ['#COUNT#' => $count]));
    }

}

$tabControl->Begin();
?>
    <form method='POST' name='<?=$options->getFormName()?>' action='<?=str_replace('&generateToken=Y', '', $APPLICATION->GetCurUri())?>'>
        <?php

        $dir = __DIR__ . '/admin/options/tabs/';

        foreach ($tabControl->tabs as $tab) {
            $path = $dir . $tab['DIV'] . '.php';
            if (is_file($path)) {

                $tabControl->BeginNextTab();
                require $path;
            }
        }

    $tabControl->End();
    $tabControl->BeginNextTab();?>
        <?php $tabControl->Buttons();?>

    <input <?php if(!$RIGHT_W) echo "disabled" ?> type='submit' name='options-save' value='<?=Loc::getMessage('BTN_OPTIONS_SAVE')?>' class='adm-btn-save'>
        <?php if(strlen($_REQUEST["back_url_settings"])>0):?>
        <input <?php if(!$RIGHT_W) echo "disabled" ?> type="button" name="Cancel" value="<?=GetMessage("MAIN_OPT_CANCEL")?>" title="<?=GetMessage("MAIN_OPT_CANCEL_TITLE")?>" onclick="window.location='<?php echo htmlspecialcharsbx(CUtil::addslashes($_REQUEST["back_url_settings"]))?>'">
        <input type="hidden" name="back_url_settings" value="<?=htmlspecialcharsbx($_REQUEST["back_url_settings"])?>">
        <?php endif?>
    <input type='submit' name='options-restore' value='<?=Loc::getMessage('BTN_OPTIONS_RESTORE')?>'>

    <?=bitrix_sessid_post()?>
        <?php $tabControl->End();?>
    </form>
<?php endif;?>