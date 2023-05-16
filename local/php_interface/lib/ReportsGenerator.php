<?php
/**
 * Created: 10.04.2023, 20:01
 * Author : Vladislav Naryzhny <desmeach@gmail.com>
 * Company: 34web Studio
 */

namespace lib;

use lib\Controllers\OrdersController;
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
}