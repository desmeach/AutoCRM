<?php
/**
 * Created: 25.04.2023, 20:12
 * Author : Vladislav Naryzhny <desmeach@gmail.com>
 * Company: 34web Studio
 */

if ($_GET['FILE']) {
    $path = $_SERVER['DOCUMENT_ROOT'] . "/include/docs/";
    $pdf = $path . substr($_GET['FILE'], 0, -4) . 'pdf';
    $png = $path . substr($_GET['FILE'], 0, -4) . 'png';
    unlink($pdf);
    unlink($png);
    file_force_download($_SERVER['DOCUMENT_ROOT'] . "/include/docs/" . $_GET['FILE']);
}

function file_force_download($file) {
    if (file_exists($file)) {
        if (ob_get_level()) {
            ob_end_clean();
        }
        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename=' . basename($file));
        header('Content-Transfer-Encoding: binary');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . filesize($file));
        readfile($file);
        exit;
    }
}