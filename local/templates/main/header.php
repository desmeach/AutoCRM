<?php
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
global $APPLICATION, $USER;
use Bitrix\Main\Page\Asset;
$unAuthPages = '/\/auth\/.*/';
$showMenu = !preg_match($unAuthPages, $_SERVER['REQUEST_URI']);
if (!$USER->IsAuthorized() && $showMenu) {
    LocalRedirect('/auth');
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=800, initial-scale=1.0">
    <link rel="apple-touch-icon" sizes="180x180" href="/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="/favicon-16x16.png">
    <link rel="manifest" href="/site.webmanifest">
    <link rel="mask-icon" href="/safari-pinned-tab.svg" color="#5bbad5">
    <meta name="msapplication-TileColor" content="#da532c">
    <meta name="theme-color" content="#ffffff">
    <meta http-equiv="Content-Type" content="text/html;charset=<?=SITE_CHARSET?>"/>
    <?php Asset::getInstance()->addCss('https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css'); ?>
    <?php Asset::getInstance()->addCss('https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css'); ?>
    <?php Asset::getInstance()->addCss('https://cdn.datatables.net/v/bs5/dt-1.13.3/fh-3.3.1/datatables.min.css'); ?>
    <?php Asset::getInstance()->addCss('https://ajax.aspnetcdn.com/ajax/jquery.ui/1.10.3/themes/base/jquery-ui.css'); ?>
    <?php Asset::getInstance()->addCss('https://cdnjs.cloudflare.com/ajax/libs/bootstrap-select/1.14.0-beta2/css/bootstrap-select.min.css'); ?>
    <?php Asset::getInstance()->addCss(SITE_TEMPLATE_PATH . '/template_styles.css'); ?>
    <?php Asset::getInstance()->addJs('https://ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js') ?>
    <?php Asset::getInstance()->addJs('https://ajax.aspnetcdn.com/ajax/jquery.ui/1.10.3/jquery-ui.min.js') ?>
    <?php Asset::getInstance()->addJs('https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js') ?>
    <?php Asset::getInstance()->addJs('https://cdn.datatables.net/v/bs5/dt-1.13.3/fh-3.3.1/datatables.min.js') ?>
    <?php Asset::getInstance()->addJs('https://cdn.jsdelivr.net/momentjs/latest/moment.min.js') ?>
    <?php Asset::getInstance()->addJs('https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js') ?>
    <?php Asset::getInstance()->addJs('/local/scripts/header_time.min.js') ?>
    <?php Asset::getInstance()->addJs('https://cdnjs.cloudflare.com/ajax/libs/bootstrap-select/1.14.0-beta2/js/bootstrap-select.min.js') ?>
    <?php $APPLICATION->ShowCSS();?>
    <?php $APPLICATION->ShowHeadStrings();?>
    <?php $APPLICATION->ShowHeadScripts();?>
<title><?php $APPLICATION->ShowTitle()?></title>
</head>
    <div id="panel"><?php $APPLICATION->ShowPanel();?></div>
    <div id="header">
        <div class="row align-items-center">
            <div class="col-1 my-3" style="margin-left: 1%">
                <a href="/orders"><img id="logo" src="/include/logos/logo.png" alt="Logo" width="150" height="41"></a>
            </div>
            <div class="col my-2 text-center" id="timer-block">
                    <h2 class="my-2" id="timer-head">Время: <span id="timer"></span></h2>
            </div>
            <div class="col-1">
                <div class="">
                    <div style="cursor: pointer;">
                        <?php
                        global $USER;
                        if ($USER->IsAuthorized()):
                            global $USER;
                            $userID = $USER->GetID();
                            $photoID = CUser::GetByID($userID)->Fetch()['PERSONAL_PHOTO'];
                            if ($photoID)
                                $photo = CFile::ResizeImageGet(
                                    $photoID,
                                    Array("width" => 55, "height" => 55),
                                )['src'];
                            else
                                $photo = '/include/avatars/default.png';
                            ?>
                        <img id="avatar"
                             src="<?=$photo?>"
                             class="dropdown-toggle"
                             data-bs-toggle="dropdown"
                             border=0
                             alt=""
                             width="55"
                             height="55"
                             style="display: block; margin-left: auto; margin-right: auto; border-radius: 50%;"/>
                        <ul class="dropdown-menu" style="box-shadow: 1px 1px 1px 0 black;" aria-labelledby="avatar">
                            <li>
                                <a href="/personal/profile/"
                                   class="dropdown-item"
                                   style="text-decoration: none;
                                   color: black;">
                                    Профиль
                                </a>
                            </li>
                            <li>
                                <a href="?logout=yes"
                                   class="dropdown-item"
                                   style="text-decoration: none;
                                   color: black;">
                                    Выйти
                                </a>
                            </li>
                        </ul>
                        <?php endif;?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="row main-layout">
        <?php if ($showMenu): ?>
        <div class="col-2 bg-info bg-opacity-10" id="left-menu">
            <?php $APPLICATION->IncludeComponent(
	"bitrix:menu",
	"vertical_autocrm",
                array(
                    "ROOT_MENU_TYPE" => "top",
                    "MAX_LEVEL" => "3",
                    "CHILD_MENU_TYPE" => "left",
                    "USE_EXT" => "Y",
                    "MENU_CACHE_TYPE" => "Y",
                    "MENU_CACHE_TIME" => "3600",
                    "MENU_CACHE_USE_GROUPS" => "Y",
                    "MENU_CACHE_GET_VARS" => array(
                    ),
                    "COMPONENT_TEMPLATE" => "vertical_autocrm",
                    "DELAY" => "N",
                    "ALLOW_MULTI_SELECT" => "N",
                    "MENU_THEME" => "site",
                    "COMPOSITE_FRAME_MODE" => "A",
                    "COMPOSITE_FRAME_TYPE" => "AUTO"
                ),
                false
            );?>
        </div>
        <?php endif;?>
        <div class="<?php if ($showMenu): ?> col-9 <?php else: ?> col-12 <?php endif;?> content-block mt-3">

