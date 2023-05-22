<?php
/**
 * Created: 14.04.2023, 11:21
 * Author : Vladislav Naryzhny <desmeach@gmail.com>
 * Company: 34web Studio
 */

require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");

use lib\ReportsGenerator;

function coverImagePath(string $pdfFileName) {
    $poster = pathinfo($pdfFileName, PATHINFO_FILENAME);
    return $_SERVER['DOCUMENT_ROOT'] . "/include/docs/$poster.png";
}

function createDocument(string $pdfFile) {
    $firstPage = '[0]'; // первая страница
    $im = new Imagick($pdfFile . $firstPage); // читаем первую страницу из файла
    $im->setImageFormat('png'); // устанавливаем формат jpg
    $im->setBackgroundColor(new ImagickPixel('transparent'));
    file_put_contents(coverImagePath($pdfFile), $im); // сохраняем файл в папку
    $im->clear(); // очищаем используемые ресурсы
}

$filename = ReportsGenerator::getOrderReport($_POST['ID']);
createDocument($_SERVER['DOCUMENT_ROOT'] . "/include/docs/$filename");
echo $filename;