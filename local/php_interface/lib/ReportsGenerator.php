<?php
/**
 * Created: 10.04.2023, 20:01
 * Author : Vladislav Naryzhny <desmeach@gmail.com>
 * Company: 34web Studio
 */

namespace lib;

use lib\Controllers\OrdersController;
use PhpOffice\PhpSpreadsheet\Reader\Html;
use PhpOffice\PhpWord\Exception\CopyFileException;
use PhpOffice\PhpWord\Exception\CreateTemporaryFileException;
use PhpOffice\PhpWord\Exception\Exception;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\Settings;
use PhpOffice\PhpWord\TemplateProcessor;

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
        $outputFileDocx = $path . 'order_document_' . $orderID . '.docx';
        $document = new TemplateProcessor($template);

        $order = OrdersController::getByID($orderID);
        $client = $order['CLIENT']['VALUE'];
        $car = $order['CAR']['VALUE'];
        $product = $order['PRODUCTS']['VALUE'];
        $branch = $order['BRANCH']['VALUE'];

        $document->setValue('orderNum', $orderID);
        $document->setValue('serviceName', $branch['NAME']);
        $document->setValue('serviceAddr', $branch['ADDRESS']['VALUE']);
        $document->setValue('servicePhone', $branch['PHONES']['VALUE'][0]);
        $document->setValue('clientName', $client['NAME']);
        $document->setValue('clientPhone', $client['PHONE']['VALUE']);
        $document->setValue('carModel', $car['MODEL']['VALUE']);
        $document->setValue('clientPhone', $client['PHONE']['VALUE']);
        $document->setValue('totalSum', $order['TOTAL_PRICE']['VALUE']);
        $document->setValue('clientPhone', $client['PHONE']['VALUE']);
        $document->setValue('clientPhone', $client['PHONE']['VALUE']);
        $document->setValue('clientPhone', $client['PHONE']['VALUE']);

        $document->saveAs($outputFileDocx);

        $PdfPath = realpath($_SERVER['DOCUMENT_ROOT'] . '/vendor/dompdf/dompdf');
        Settings::setPdfRendererPath($PdfPath);
        Settings::setPdfRendererName(Settings::PDF_RENDERER_DOMPDF);

        $phpWord = IOFactory::load($outputFileDocx); // путь к DOCX файлу
        $xmlWriter = IOFactory::createWriter($phpWord , 'PDF');
        $phpWord->setDefaultFontName('dejavu sans');

        $pdfFile = "order_document_$orderID.pdf";
        $xmlWriter->save($_SERVER['DOCUMENT_ROOT'] . "/include/docs/$pdfFile");
        return $pdfFile;
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