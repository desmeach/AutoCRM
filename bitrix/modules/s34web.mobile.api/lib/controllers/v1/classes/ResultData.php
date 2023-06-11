<?php
namespace s34web\Mobile\Api\controllers\v1\classes;
/**
 * Created by PhpStorm.
 * User: Alex
 * Date: 11.01.2018
 * Time: 21:23
 */

class ResultData
{

    private array $buffer;
    private $data_keys;
    /**
     * @var int
     */
    private $_statusCode;

    public function __construct()
    {
        $this->_statusCode = 200;
        $this->buffer = array("success"=>true);
    }

    public function setSuccess($status=true)
    {
        $this->buffer["success"] = $status;
    }

    /**
     * @return array
     */
    public function getErrors()
    {
        return $this->buffer["error_message"];
    }

    /**
     * Установка ошибки завершения токена
     */
    public function setStatusAuthTokenExpectedError()
    {
        $this->setStatusCode(401);
        $this->setSuccess(false);
    }
    public function setStatusAuthError()
    {
        $this->setStatusCode(400);
        $this->setSuccess(false);
    }

  /**
   * Установка ошибки статуса авторизации
   */
   public function setStatusBadRequestError()
  {
    $this->setStatusCode(400);
    $this->setSuccess(false);
   }

    /**
     * Установка ошибки ресурс не найден
     */
    public function setStatusNotFoundError()
    {
        $this->setStatusCode(404);
        $this->setSuccess(false);
    }

  /**
   * Установка ошибки сервера
   */
    public function setStatusServerError()
    {
      $this->setStatusCode(500);
      $this->setSuccess(false);
    }

    public function setStatusNotRelease()
    {
        $this->setStatusCode(501);
        $this->setSuccess(false);
    }

    /**
     * @param int $status_code 200
     */
    public function setStatusCode(int $status_code=200)
    {
        $this->_statusCode = $status_code;
    }

    public function getStatusCode()
    {
        return $this->_statusCode;
    }

    private function setErrorSimple2($code, $message, $type="")
    {
        if(!is_array($this->buffer["error_message"]))
            $this->buffer["error_message"] = [];
        $t = ["text"=>$message, "code"=>$code];
        /*if($type!=="")
            $t["type"] = $type;*/
        $this->buffer["error_message"][] = $t;
    }

    private function setErrorSimple($code,$params=[])
    {
        $this->setErrorSimple2($code, cErrors::getText($code,$params));
    }

    /**
     *  Добавление массива ошибок либо код ошибки в базе
     * @param $errors = array or int
     * @param bool $message
     */
    public function setErrors($errors, $message=false)
    {
        if($message != false)
            $this->setErrorSimple2("-1", $message, "unknown");
        elseif(isset($errors)) {
                if (is_array($errors)) {

                    foreach ($errors as $item) {
                        if (is_array($item) && count($item) > 0) {
                            $this->setErrorSimple($item[0],$item[1]);
                        }elseif(intval($item)>0) {
                            $this->setErrorSimple($item);
                        }else
                            $this->setErrorSimple2("-1", $item, "unknown");
                    }
                } else {
                    $this->setErrorSimple($errors);
                }
            }
        $this->setSuccess(false);
        $this->setStatusCode(400);
    }

    public function setData($data)
    {
        if(!is_array($data)){
            $data = (array) $data;
        }
        $keys = array_keys($data);

        if(count($keys)>0){
            $this->data_keys = $keys;
            if(!is_array($this->buffer["data"]))
                $this->buffer["data"] = array();
            $this->buffer["data"] += $data;
            $this->setSuccess();
        }elseif(cGeneral::IS_DATA_EMPTY_ERROR){

            $this->setStatusNotFoundError();

            //$this->setErrorSimple2(cErrors::NO_DATA, cErrors::getText(cErrors::NO_DATA));
        }else{
            $this->buffer["data"] = [];
            $this->setSuccess();
        }
    }

    /**
     * @return array
     */
    public function getResult()
    {
        return $this->buffer;
    }

    public function getData()
    {
        $data = array();
        if(count($this->data_keys)>0 && !is_array($this->buffer["data"]))
            $this->buffer["data"] = array();
        if(count($this->data_keys)>0)
            foreach ($this->data_keys as $key)
            {
                $data[$key] = $this->buffer["data"][$key];
            }
        return $data;
    }

    public function isSuccess()
    {
        return $this->buffer["success"]==true;
    }

    public function setParam($param_name, $param_value)
    {
        $this->buffer[$param_name]=$param_value;
    }

    public function getParam($param_name)
    {
        return $this->buffer[$param_name];
    }


}