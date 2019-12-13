<?php
namespace Multitext;

class Request {
    private static $instance;

    public $route;
    public $params;

    public static function getInstance() {
        return self::$instance;
    }

    public static function generateFromGlobals() {
        self::$instance = new self();
        self::$instance->init();
        return self::$instance;
    }

    private function __construct() {}

    public function init() {
        foreach($_SERVER as $key => $value)
        {
            $this->{$this->toCamelCase($key)} = $value;
        }
        $this->params = $_REQUEST;
        $this->route = $this->requestUri ? : '/';
    }

    private function toCamelCase($string)
    {
        $result = strtolower($string);

        preg_match_all('/_[a-z]/', $result, $matches);
        foreach($matches[0] as $match)
        {
            $c = str_replace('_', '', strtoupper($match));
            $result = str_replace($match, $c, $result);
        }
        return $result;
    }

    public function getRoute() {
        return $this->route;
    }
    public function getParams() {
        return $this->params;
    }
    public function addParam($key, $value) {
        $this->params[$key] = $value;
    }
}