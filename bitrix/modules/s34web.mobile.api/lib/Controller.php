<?php
namespace s34web\Mobile\Api;

use Bitrix\Main\Localization\Loc;

Loc::loadLanguageFile(__FILE__);

class Controller extends Router
{
    const ROOT_DIR_CONTROLLERS = 'controllers';

    const OBJECT_ORIENTED = 'OBJECT_ORIENTED';
    const FILE = 'FILE';
    const OBJECT_ORIENTED_FILE = 'OBJECT_ORIENTED_FILE';

    private $mode;

    // MAIN METHODS

    public function run()
    {

        if ($this->getMode()) {

            switch ($this->getMode()) {

                case self::OBJECT_ORIENTED:

                    $this->startObjectOriented();
                    break;

                case self::FILE:
                    $this->startFile();
                    break;

                case self::OBJECT_ORIENTED_FILE:
                    $this->startCombined();
                    break;
            }

        } else {

            // Default operating mode
            $this->startObjectOriented();
        }
    }


    // ADDITIONAL METHODS

    // Operating mode
    private function getMode()
    {
        if (!$this->mode) {
            $this->mode = parent::getParameter()->getValue('OPERATING_MODE');
        }

        return $this->mode;
    }

    // Start object-oriented
    private function startObjectOriented()
    {
        $controller = false;

        // Get name and version controller
        if (parent::getApiVersion()) {

            $path = __DIR__ . DIRECTORY_SEPARATOR . self::ROOT_DIR_CONTROLLERS . DIRECTORY_SEPARATOR . parent::getApiVersion() . DIRECTORY_SEPARATOR . ucfirst(strtolower(parent::getController())) . '.php';

            if (file_exists($path)) {

                $controller = __NAMESPACE__ . '\\' . self::ROOT_DIR_CONTROLLERS . '\\' . parent::getApiVersion() . '\\' . ucfirst(strtolower(parent::getController()));
            } else {
                Response::BadRequest(Loc::getMessage('CLASS_NOT_FOUND', ['#OBJECT#' => ucfirst(parent::getController())]) . ' [' . parent::getApiVersion() . ']');
            }

        } else {

            $path = __DIR__ . DIRECTORY_SEPARATOR . self::ROOT_DIR_CONTROLLERS . DIRECTORY_SEPARATOR . ucfirst(strtolower(parent::getController())) . '.php';

            if (file_exists($path)) {
                $controller = __NAMESPACE__ . '\\' . self::ROOT_DIR_CONTROLLERS . '\\' . ucfirst(strtolower(parent::getController()));
            } else {
                Response::BadRequest(Loc::getMessage('CLASS_NOT_FOUND', ['#OBJECT#' => ucfirst(parent::getController())]));
            }
        }
        $controllerObject = new $controller();

        if (method_exists($controllerObject, parent::getAction())) {

            // Request to log
            $this->log();

            // Start action
            $action = parent::getAction();
            $controllerObject->$action();

        } else {

            Response::BadRequest(Loc::getMessage('METHOD_NOT_FOUND', ['#OBJECT#' => ucfirst(parent::getController()), '#METHOD#' => parent::getAction()]));
        }
    }

    // Start file
    private function startFile()
    {
        // Get path and version controller
        if (parent::getApiVersion()) {

            $path = $_SERVER['DOCUMENT_ROOT'] . DIRECTORY_SEPARATOR . parent::getApiPath() . DIRECTORY_SEPARATOR . parent::getApiVersion() . DIRECTORY_SEPARATOR . parent::getController() . DIRECTORY_SEPARATOR . parent::getAction() . '.php';

            if (!file_exists($path)) {
                Response::BadRequest(Loc::getMessage('CLASS_NOT_FOUND', ['#OBJECT#' => ucfirst(parent::getController())]) . ' [' . parent::getApiVersion() . ']');
            }

        } else {

            $path = $_SERVER['DOCUMENT_ROOT'] . DIRECTORY_SEPARATOR . parent::getApiPath() . DIRECTORY_SEPARATOR . parent::getController() . DIRECTORY_SEPARATOR . parent::getAction() . '.php';

            if (!file_exists($path)) {
                Response::BadRequest(Loc::getMessage('CLASS_NOT_FOUND', ['#OBJECT#' => ucfirst(parent::getController())]));
            }
        }

        if (file_exists($path)) {

            // Request to log
            $this->log();

            // Start controller
            require_once $path;

        } else {

            Response::BadRequest(Loc::getMessage('METHOD_NOT_FOUND', ['#OBJECT#' => ucfirst(parent::getController()), '#METHOD#' => parent::getAction()]));
        }
    }

    // Start combined mode
    private function startCombined()
    {
        $controller = false;

        // Get name and version controller
        if (parent::getApiVersion()) {

            $path = __DIR__ . DIRECTORY_SEPARATOR . self::ROOT_DIR_CONTROLLERS . DIRECTORY_SEPARATOR . parent::getApiVersion() . DIRECTORY_SEPARATOR . strtolower(parent::getController()) . '.php';

            if (file_exists($path)) {

                $controller = __NAMESPACE__ . '\\' . self::ROOT_DIR_CONTROLLERS . '\\' . parent::getApiVersion() . '\\' . ucfirst(strtolower(parent::getController()));

            } else {

                $path = $_SERVER['DOCUMENT_ROOT'] . DIRECTORY_SEPARATOR . parent::getApiPath() . DIRECTORY_SEPARATOR . parent::getApiVersion() . DIRECTORY_SEPARATOR . parent::getController() . DIRECTORY_SEPARATOR . parent::getAction() . '.php';

                if (!file_exists($path)) {
                    Response::BadRequest(Loc::getMessage('CLASS_NOT_FOUND', ['#OBJECT#' => ucfirst(parent::getController())]) . ' [' . parent::getApiVersion() . ']');
                }
            }

        } else {

            $path = __DIR__ . DIRECTORY_SEPARATOR . self::ROOT_DIR_CONTROLLERS . DIRECTORY_SEPARATOR . strtolower(parent::getController()) . '.php';

            if (file_exists($path)) {

                $controller = __NAMESPACE__ . '\\' . self::ROOT_DIR_CONTROLLERS . '\\' . ucfirst(strtolower(parent::getController()));

            } else {

                $path = $_SERVER['DOCUMENT_ROOT'] . DIRECTORY_SEPARATOR . parent::getApiPath() . DIRECTORY_SEPARATOR . parent::getController() . DIRECTORY_SEPARATOR . parent::getAction() . '.php';

                if (!file_exists($path)) {
                    Response::BadRequest(Loc::getMessage('CLASS_NOT_FOUND', ['#OBJECT#' => ucfirst(parent::getController())]));
                }
            }
        }

        if ($controller) {

            $controllerObject = new $controller();

            if (method_exists($controllerObject, parent::getAction())) {

                // Request to log
                $this->log();

                // Start action
                $action = parent::getAction();
                $controllerObject->$action();

            }

        } elseif (file_exists($path)) {

            // Request to log
            $this->log();

            // Start controller
            require_once $path;

        } else {

            Response::BadRequest(Loc::getMessage('METHOD_NOT_FOUND', ['#OBJECT#' => ucfirst(parent::getController()), '#METHOD#' => parent::getAction()]));
        }
    }

    // Add request to log
    private function log()
    {
        if (parent::getParameter()->getValue('SUPPORT_USE_LOG') == 'Y') {

            $pathLog = $_SERVER['DOCUMENT_ROOT'] . parent::getParameter()->getValue('SUPPORT_LOG_PATH');

            if (is_file($pathLog)) {

                file_put_contents($pathLog, print_r(Request::get(), 1), FILE_APPEND | LOCK_EX);
            }
        }
    }
}