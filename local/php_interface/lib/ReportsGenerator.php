<?php
/**
 * Created: 10.04.2023, 20:01
 * Author : Vladislav Naryzhny <desmeach@gmail.com>
 * Company: 34web Studio
 */

namespace lib;

use Bitrix\Main\Type\DateTime;
use lib\Controllers\OrdersController;
use lib\Controllers\ProductsController;
use PhpOffice\PhpSpreadsheet\Reader\Html;
use PhpOffice\PhpWord\Exception\CopyFileException;
use PhpOffice\PhpWord\Exception\CreateTemporaryFileException;
use PhpOffice\PhpWord\Exception\Exception;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\Shared\ZipArchive;
use PhpOffice\PhpWord\TemplateProcessor;
use Ilovepdf\Ilovepdf;

require_once $_SERVER['DOCUMENT_ROOT'] . '/vendor/autoload.php';

class ReportsGenerator {
    const DOCS_PATH = '/include/docs/';
    /**
     * @throws CopyFileException
     * @throws CreateTemporaryFileException|Exception
     */
    public static function getOrderReport(int $orderID) {
        $path = $_SERVER['DOCUMENT_ROOT'] . self::DOCS_PATH;
        $template = $path . 'order_document.docx';
        $timestamp = new DateTime();
        $timestamp = MakeTimeStamp($timestamp->toString());
        $outputFileDocx = $path . "order_document_" . $orderID . "_$timestamp.docx";
        $document = new TemplateProcessor($template);

        $order = OrdersController::getByID($orderID);
        $client = $order['CLIENT']['VALUE'];
        $car = $order['CAR']['VALUE'];
        $products = $order['PRODUCTS']['VALUE'];
        $branch = $order['BRANCH']['VALUE'];

        $document->setValue('orderNum', $orderID);
        $document->setValue('serviceName', $branch['NAME']);
        $document->setValue('serviceAddr', $branch['ADDRESS']['VALUE']);
        $document->setValue('servicePhone', $branch['PHONES']['VALUE'][0]);
        $document->setValue('dateAccept', $order['DATE_ACCEPT']['VALUE']);
        $document->setValue('dateStart', $order['DATE_START']['VALUE']);
        $document->setValue('dateEnd', $order['DATE_END']['VALUE']);
        $document->setValue('clientName', $client['NAME']);
        $document->setValue('clientPhone', $client['PHONE']['VALUE']);
        $document->setValue('carModel', $car['BRAND']['VALUE']);
        $document->setValue('carYear', $car['YEAR']['VALUE']);
        $document->setValue('carMileage', $order['MILEAGE']['VALUE']);
        $document->setValue('carBody', $car['BODY']['VALUE']);
        $document->setValue('carEngine', $car['ENGINE']['VALUE']);
        $document->setValue('carChassis', $car['CHASSIS']['VALUE']);
        $document->setValue('carVIN', $car['NAME']);
        $document->setValue('carNumber', $car['REG_NUM']['VALUE']);
        $document->setValue('clientPhone', $client['PHONE']['VALUE']);
        $document->setValue('totalSum', $order['TOTAL_PRICE']['VALUE']);
        $document->setValue('managerName', $order['MANAGER']['VALUE']['LAST_NAME'] .
            ' ' . $order['MANAGER']['VALUE']['NAME']);
        $document->setValue('masterComment', $order['COMMENT_MASTER']['VALUE']);
        $document->setValue('masterMainName', $order['MASTER']['VALUE']['NAME']);
        $document->setValue('masterName', $order['MASTER']['VALUE']['NAME']);

        $document_with_table = new PhpWord();
        $section = $document_with_table->addSection();
        $table = $section->addTable(
            [
            'borderSize' => 6,
            'borderColor' => '000000',
            'afterSpacing' => 0,
            'Spacing'=> 0,
            'cellMargin'=> 0
            ]
        );
        $table->addRow();
        $table->addCell(1200)->addText("Код", ['bold' => true], array('alignment' => 'center'));
        $table->addCell(4500)->addText("Наименование работ", ['bold' => true], array('alignment' => 'center'));
        $table->addCell(900)->addText("Кол-во", ['bold' => true], array('alignment' => 'center'));
        $table->addCell(1350)->addText("Норма времени н/ч", ['bold' => true], array('alignment' => 'center'));
        $table->addCell(1400)->addText("Стоимость (руб.)", ['bold' => true], array('alignment' => 'center'));
        $table->addCell(1400)->addText("Сумма (руб.)", ['bold' => true], array('alignment' => 'center'));
        foreach ($products as $product) {
            $product = ProductsController::getByID($product['ID']);
            $table->addRow();
            $table->addCell(1200)->addText($product['ID']);
            $table->addCell(4500)->addText($product['NAME']);
            $table->addCell(900)->addText(1, [], array('alignment' => 'center'));
            $table->addCell(1350)->addText($product['WORKING_HOUR']['VALUE'], [], array('alignment' => 'center'));
            $table->addCell(1400)->addText($product['PRICE']['VALUE'], [], array('alignment' => 'center'));
            $table->addCell(1400)->addText($product['PRICE']['VALUE'], [], array('alignment' => 'center'));
        }

        $objWriter = \PhpOffice\PhpWord\IOFactory::createWriter($document_with_table);

        $fullxml = $objWriter->getWriterPart('Document')->write();

        $tablexml = preg_replace('/^[\s\S]*(<w:tbl\b.*<\/w:tbl>).*/', '$1', $fullxml);

        $document->setValue('tableBody', $tablexml);
        ob_clean();
        $document->saveAs($outputFileDocx);

        $ilovepdf = new Ilovepdf(
            'project_public_73a11025f0da0a09fa74cc7b6ebbc973_iSHfM698d48cc6ab40ecf64e20e2b20c5fb23',
            'secret_key_2884108f682010dd6cb7a4e3b1424f6b_QleLHa40ed75e282f4427536ceb93f0bf1ca6');

        $myTaskConvertOffice = $ilovepdf->newTask('officepdf');
        $file = $myTaskConvertOffice->addFile($outputFileDocx);
        $myTaskConvertOffice->execute();
        $myTaskConvertOffice->download($_SERVER['DOCUMENT_ROOT'] . '/include/docs/');

        $myTaskConvertPDF = $ilovepdf->newTask('pdfjpg');
        $file1 = $myTaskConvertPDF->addFile($_SERVER['DOCUMENT_ROOT'] . '/include/docs/' . "order_document_$orderID" . "_$timestamp.pdf");
        $myTaskConvertPDF->execute();
        $myTaskConvertPDF->download($_SERVER['DOCUMENT_ROOT'] . '/include/docs/');
        $zip = new ZipArchive;
        if ($zip->open($_SERVER['DOCUMENT_ROOT'] . '/include/docs/output.zip') === TRUE) {
            $zip->extractTo($_SERVER['DOCUMENT_ROOT'] . '/include/docs/');
            $zip->close();
        }
        return "order_document_$orderID" . "_" . $timestamp;
    }

    public static function getAnalyticReport($data, $entity) {
        $headers = json_decode($data['headers'], true);
        $tableBody = $data['table-body'];
        $tableHead = '<thead><tr>';
        foreach ($headers as $header) {
            $tableHead .= '<th scope="col">' . $header . '</th>';
        }
        $tableHead .= '</tr></thead>';
        $table = '<table>' . $tableHead . $tableBody . '</table>';

        $reader = new Html();
        $spreadsheet = $reader->loadFromString($table);
        $columns = [
            1 => 'A',
            2 => 'B',
            3 => 'C',
            4 => 'D',
            5 => 'E',
            6 => 'F',
            7 => 'G',
            8 => 'H'
        ];
        $curCol = 1;
        $activeWorksheet = $spreadsheet->setActiveSheetIndex(0);
        foreach ($headers as $ignored) {
            $activeWorksheet->getColumnDimension($columns[$curCol])->setAutoSize(true);
            $curCol++;
        }
        $writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, 'Xlsx');

        $entityName = match($entity) {
            'clients' => 'клиентам',
            'orders' => 'заказам',
            'products' => 'услугам',
        };
        $filename = "Статистика по $entityName " . date('d.m.Y') . '.xlsx';
        $writer->save($_SERVER['DOCUMENT_ROOT'] . "/include/docs/$filename");
        return $filename;
    }
}