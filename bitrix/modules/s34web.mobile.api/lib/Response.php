<?php

namespace s34web\Mobile\Api;

class Response
{
    private static function setHeaders()
    {
        if(date('Y')=="2020")
            header('Dev: 34web 2020');
        else
            header('Dev: 34web 2020-'.date('Y'));
        header('Dev-Support: alex@34web.ru');
        header('Content-Type: application/json; charset=utf-8');
    }

    public static function ShowResult($data,$arParams=[])
    {
        self::setHeaders();
        header('HTTP/1.1 200');

        if(array_key_exists("data", $data))
        {
            $buf = $data["data"];
        }else{
            $buf = $data;
        }

        $result = json_encode($buf);
        if ($error = self::ckeckError()) {
            header('HTTP/1.1 500');
            $result = json_encode(['errors' => ["text"=>$error]]);
        }

        echo $result;
        die();
    }

    public static function SendResult($data, $status=200, $arParams=[])
    {
      self::setHeaders();
      header('HTTP/1.1 '.$status);

      if(is_array($data) && array_key_exists("data", $data))
      {
        $buf = $data["data"];
      }else{
        $buf = $data;
      }

      $result = json_encode($buf);
      if ($error = self::ckeckError()) {
        header('HTTP/1.1 500');
        $result = json_encode(["success"=> false,'error_message' => ["text"=>$error]]);
      }

      echo $result;
      die();
    }

    public static function NoResult($message = '')
    {
        $message = ($message) ? $message : ['success"=> false',"error_message"=>["text"=>'No Result']];
        self::SendResult($message,200);
    }

    public static function BadRequest($message = '')
    {
        if(empty($message))
          $message = ['success"=> false',"error_message"=>["text"=>'Bad Request']];
        elseif(!is_array($message))
        {
          $message = ["success"=> "false", "error_message"=>["text"=>$message]];
        }

        self::SendResult($message,400);
    }

    public static function NotFoundRequest($message = '')
    {
        if(empty($message))
            $message = ['success"=> false',"error_message"=>["text"=>'Not Found Request']];
        elseif(!is_array($message))
        {
            $message = ["success"=> "false", "error_message"=>["text"=>$message]];
        }

        self::SendResult($message,404);
    }

    public static function UnAuthRequest($message = '')
    {
      if(empty($message))
        $message = ['success"=> false',"error_message"=>["text"=>'UnAUTHORIZE']];
      elseif(!is_array($message))
      {
        $message = ["success"=> "false", "error_message"=>["text"=>$message]];
      }

      self::SendResult($message,401);
    }

    public static function DenyAccess()
    {
      self::SendResult(['success"=> false',"error_message"=>["text"=>'Доступ запрещён']],401);
    }

    private static function ckeckError() {
        switch (json_last_error()) {

            case JSON_ERROR_DEPTH:
                $result = 'JSON_ERROR_DEPTH';
                break;
            case JSON_ERROR_STATE_MISMATCH:
                $result = 'JSON_ERROR_STATE_MISMATCH';
                break;
            case JSON_ERROR_CTRL_CHAR:
                $result = 'JSON_ERROR_CTRL_CHAR';
                break;
            case JSON_ERROR_SYNTAX:
                $result = 'JSON_ERROR_SYNTAX';
                break;
            case JSON_ERROR_UTF8:
                $result = 'JSON_ERROR_UTF8';
                break;
            default:
                $result = false;
        }
        return $result;
    }
}