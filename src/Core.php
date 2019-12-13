<?php
namespace Multitext;

class Core extends BaseCore {
    protected $params;
    protected $routes;

    public function __construct($config)
    {
        $this->params = $config['params'];
        $this->routes = $config['routes'];
    }

    public function handle(Request $request)
    {
        try {
            $router = new Router($request);
            list($controllerName, $actionName) = $router->processRoute($this->routes);

            $controllerParams = $this->params['controllers'];
            $controllerPath = rtrim($controllerParams['path'], '/') . '/';

            include_once $controllerPath . $controllerName . '.php';

            $controllerNamespace = rtrim($controllerParams['namespace'], '\\') . '\\';
            $controllerName = $controllerNamespace . $controllerName;
            $controller = new $controllerName();
            list($templateViev, $contentView, $data) = $controller->handle($request, $actionName);

            if ($templateViev || $contentView) {
                (new View($this->params['view']))->generate($templateViev, $contentView, $data, $this->params['view']);
            }
        } catch (Exception404 $e) {
            $this->error404();
        }
    }
    protected function error404($request) {
        $host = 'http://'. $request->httpHost .'/';
        header('HTTP/1.1 404 Not Found');
        header("Status: 404 Not Found");
        header('Location:'.$host.'404');
        include '404.php';
        exit(0);
    }
}