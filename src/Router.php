<?php
namespace Multitext;
class Router {
    protected $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function processRoute($routes) {
        $path = trim($this->request->getRoute(), '/');
        foreach ($routes as $routeParams) {
            $pattern = trim($routeParams['path'], '/');
            $result = $this->processPath($path, $pattern);
            if ($result !== false) {
                if (is_array($result)) {
                    $this->addRequestParams($result);
                }
                return [ $routeParams['controller'], $routeParams['action'] ];
            }
        }
        throw new Exception404();
    }
    public function addRequestParams($params) {
        foreach ($params as $key => $value) {
            $this->request->addParam($key, $value);
        }
    }
    public function processPath($path, $pattern) {
        $tokens = preg_split('/[\/]+/', $path);
        $rules = preg_split('/[\/]+/', $pattern);
        $pathParams = [];
        if (count($tokens) != count($rules)) {
            return false;
        }
        reset($tokens);
        foreach ($rules as $rule) {
            $token = current($tokens);
            if (preg_match('/^:([^:]*)$/', $rule, $matches)) {
                $pathParams[$matches[1]] = $token;
            } else if ($token != $rule) {
                return false;
            }
            next($tokens);
        }
        return $pathParams ? : true;
    }
}
class Exception404 extends \Exception {

}