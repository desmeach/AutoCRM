<?php


namespace s34web\Mobile\Api;


class Router extends Init
{
    private static $apiPath;
    private static $apiVersion;
    private static $controller;
    private static $action;
    private static $params;

    // MAIN METHODS

    public function start()
    {
        $path_parts = explode('/', trim($_SERVER['REQUEST_URI'], '/'));

        // Api path
        self::$apiPath = strtolower(current($path_parts));
        array_shift($path_parts);

        // Get version
        if ($this->checkUseVersion()) {

            if (current($path_parts)) {
                self::$apiVersion = strtolower(current($path_parts));
                array_shift($path_parts);
            }
        }

        // Get controller
        if (current($path_parts)) {

            self::$controller = strtolower(current($path_parts));
            array_shift($path_parts);
        }

        // Get action
        if (current($path_parts)) {

            self::$action = strtolower(current($path_parts));
            array_shift($path_parts);
        }

        // Get params
        switch (parent::getMethod()) {
            case 'GET':
                if (count($path_parts) > 0)
                {
                    try {
                        // if original get-request
                        if (strstr($_SERVER['REQUEST_URI'], '?', false) !== false) {
                            $path_parts = [];
                            parse_str($_SERVER['QUERY_STRING'], $path_parts);
                        }
                    } catch (\Exception $ex)
                    {
                        Response::BadRequest();
                    }
                }
                break;

            case 'POST':
                try {
                    if (self::isJsonType()) {
                        $path_parts = json_decode(file_get_contents('php://input'), true);
                    } else {
                        $text = file_get_contents('php://input');

                        parse_str($text, $path_parts);
                        if (count($_POST) > 0) {
                            $path_parts = $path_parts + $_POST;
                        }
                    }
                } catch (\Exception $ex)
                {
                    Response::BadRequest();
                }
                break;

            case 'PUT':
                try {
                    if (self::isJsonType()) {
                        $path_parts = json_decode(file_get_contents('php://input'), true);
                    } else {


                          $path_parts = [];
                          self::parse_raw_http_request($path_parts);

                    }
                } catch (\Exception $ex)
                {
                    Response::BadRequest();
                }
                break;

            case 'DELETE':
                if (self::isJsonType()) {
                    $path_parts = json_decode(file_get_contents('php://input'), true);
                }
                break;

            case 'OPTIONS':
                if (self::isJsonType()) {
                    $path_parts = json_decode(file_get_contents('php://input'), true);
                }
                break;
        }

        self::$params = $path_parts;


        // Run controller
        if ($this->getController() && $this->getAction()) {

            $controller = new Controller();

            $controller->run();

        } else {

            Response::BadRequest();
        }

        die();
    }

    function parse_raw_http_request(array &$a_data)
    {
        // read incoming data
        $input = file_get_contents('php://input');

        // grab multipart boundary from content type header
        preg_match('/boundary=(.*)$/', $_SERVER['CONTENT_TYPE'], $matches);
        $boundary = $matches[1];

        // split content by boundary and get rid of last -- element
        $a_blocks = preg_split("/-+$boundary/", $input);
        array_pop($a_blocks);

        // loop data blocks
        foreach ($a_blocks as $id => $block)
        {
            if (empty($block))
                continue;

            // you'll have to var_dump $block to understand this and maybe replace \n or \r with a visibile char

            // parse uploaded files
            if (strpos($block, 'application/octet-stream') !== FALSE)
            {
                // match "name", then everything after "stream" (optional) except for prepending newlines
                preg_match('/name=\"([^\"]*)\".*stream[\n|\r]+([^\n\r].*)?$/s', $block, $matches);
            }
            // parse all other fields
            else
            {
                // match "name" and optional value in between newline sequences
                preg_match('/name=\"([^\"]*)\"[\n|\r]+([^\n\r].*)?\r$/s', $block, $matches);
            }
            $a_data[$matches[1]] = $matches[2];
        }
    }

    private function isJsonType()
    {
        $type = $_SERVER['CONTENT_TYPE'];

        if($type) {
            $types = explode(";", $type, 2);
            return trim($types[0]) == "application/json";
        }else
            return false;
    }
    // ADDITIONAL METHODS


    // PARAMETERS

    private function checkUseVersion()
    {
        return (parent::getParameter()->getValue('USE_VERSIONS') == 'Y') ? true : false;
    }
    static public function getApiPath()
    {
        return self::$apiPath;
    }
    static public function getApiVersion()
    {
        return self::$apiVersion;
    }
    static public function getController()
    {
        return self::$controller;
    }
    static public function getAction()
    {
        return self::$action;
    }
    static public function getParameters()
    {
        return self::$params;
    }
}