<?php
/**
 * Created: 18.02.2023, 13:46
 * Author : Alex Rilkov <alex@34web.ru>
 * Company: 34web Studio
 */

use OpenApi\Util;

if(!$_SERVER["DOCUMENT_ROOT"])
    $_SERVER["DOCUMENT_ROOT"] = dirname(__DIR__,2);
if($_SERVER["HTTP_HOST"]=="stimul.tel")
    error_reporting(0);
require("../../vendor/autoload.php");

$openapi = \OpenApi\Generator::scan(Util::finder([ $_SERVER["DOCUMENT_ROOT"].'/local/modules/s34web.stimul.api/']));

file_put_contents(__DIR__."/api.json", $openapi->toJson());
header('Content-Type: application/json');
echo file_get_contents(__DIR__."/api.json");
/*
 * use Doctrine\Common\Annotations\AnnotationReader;
 //..............
 AnnotationReader::addGlobalIgnoredNamespace("OA");
 */