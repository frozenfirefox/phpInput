<?php

/*************************参数接收封装类**********************************
    @author Alpha
    @date  2018/07/17 12:05:11
    @version 1.0.0
    @description php接收参数的通用方法 post get delete put patch 暂时不支持别的传参方式

**********************参数接收方法****************************************/
class Request{
    private static $instance;
    private static $method;

    function __construct(){
        self::$method = $_SERVER['REQUEST_METHOD'];
    }

    public static function _input(){
        switch (self::$method) {
            case 'POST':
                $data = $_POST;
                break;
            case 'PUT':
            case 'PATCH':
            case 'DELETE':
                $data = self::parseParam();
                break;
            default:
                $data = $_GET;
                break;
        }
        return ['data' => $data, 'method' => self::$method];
    }

    /**
     * 解析参数
     * @return [type] [description]
     */
    private static function parseParam(){
        $putData = file_get_contents("php://input");
        $resultData = json_decode($putData,true);

        $resultData = [];

        if($resultData && is_array($resultData)){
            //解析IOS提交的PUT数据
            return $resultData;
        }

        if(!strstr($putData,"\r\n")){
            //解析本地测试工具提交的PUT数据
            parse_str($putData,$putData);
            return $resultData;
        }

        $putData = explode("\r\n",$putData);

        //解析PHP CURL提交的PUT数据
        foreach($putData as $key=>$data){
            if(substr($data,0,20) == 'Content-Disposition:'){
                // preg_match('/\"(?:.*)\"/',$data,$matchName);
                preg_match('/.*\"(.*)\"/',$data,$matchName);
                $resultData[$matchName[1]] = $putData[$key+2];
            }
        }

        return $resultData;
    }

    /********一个骚气的输出方法*******/
    public static function dd($var){
        echo '<pre/>';
        var_dump($var);
        die;
    }

    public static function getInstance(){
        if(!self::$instance instanceof self){
            self::$instance = new Request();
        }
        return self::$instance;
    }
    //防止对象克隆
    private function __clone(){

    }
}

/************************使用区*******************************/
$request    = Request::getInstance();
$data       = $request::_input();
$request::dd($data);
/************************end使用区*******************************/