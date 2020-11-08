<?php
namespace Jy;

class JSONResponse
{

    private $userData;
    private $code;
    private $message;
    private $data;

    /**
     * 构造函数
     * @param $data
     */
    public function __construct($data)
    {
        $this->data = $data;

        $this->code = $data['code'] ?? null;
        $this->message = $data['message'] ?? null;
        $this->userData = $data['userData'] ?? null;
    }

    function __toString()
    {
        return json_encode($this->data, JSON_UNESCAPED_UNICODE) ?: json_last_error_msg();
    }

    public function getData()
    {
        return $this->data;
    }

    public function getCode()
    {
        return $this->code;
    }

    public function getMessage()
    {
        return $this->message;
    }

    public function getUserData()
    {
        return $this->userData;
    }
}
